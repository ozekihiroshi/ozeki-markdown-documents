<?php
// Run only in a disposable, synthetic WordPress instance.
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require '/var/www/html/wp-load.php';
if (!str_starts_with(home_url(), 'http://owr-hypothesis-')) {
    throw new RuntimeException('Disposable fixture required');
}
wp_set_current_user(1);
if (($argv[1]??'')==='unavailable') {
    if (function_exists('wpmem_is_blocked')) throw new RuntimeException('Deactivate WP-Members before this check');
    $id=wp_insert_post(['post_type'=>'ozmd_document','post_status'=>'publish','post_title'=>'Unavailable provider']);
    (new OzekiMarkdownDocuments\Content\MarkdownSource())->store($id,'# UNAVAILABLE-SECRET');
    update_post_meta($id,'_wpmem_block','1');
    (new OzekiMarkdownDocuments\Rendering\CachedDocumentRenderer(new OzekiMarkdownDocuments\Rendering\MarkdownRenderer()))->render($id);
    $checks=[];
    foreach ([0,1] as $uid) {wp_set_current_user($uid);foreach ([false,true] as $embed) $checks[$uid.'.'.($embed?'embed':'direct')]=!str_contains(access_render($id,$embed),'UNAVAILABLE-SECRET');}
    $checks['administrator_explanation']=str_contains(access_render($id,true),'cannot currently be checked');
    update_post_meta($id,'_wpmem_block','0');wp_set_current_user(0);
    $checks['explicit_public_without_provider']=str_contains(access_render($id,true),'UNAVAILABLE-SECRET');
    delete_post_meta($id,'_wpmem_block');$settings=get_option('wpmembers_settings');$settings['block']['ozmd_document']=1;update_option('wpmembers_settings',$settings);
    $checks['recorded_default_without_provider']=!str_contains(access_render($id,true),'UNAVAILABLE-SECRET');
    echo wp_json_encode(['checks'=>$checks,'pass'=>!in_array(false,$checks,true)]);exit(in_array(false,$checks,true)?1:0);
}
$reader = username_exists('access-reader') ?: wp_insert_user(['user_login'=>'access-reader','user_pass'=>'isolated-only-123','user_email'=>'access@example.invalid','role'=>'subscriber']);
update_user_meta($reader, 'active', 1);
$ids = [];
foreach (['public','member','password','private','draft'] as $kind) {
    $id = wp_insert_post(['post_type'=>'ozmd_document','post_title'=>'Access '.$kind,'post_status'=>in_array($kind,['private','draft'],true)?$kind:'publish','post_password'=>$kind==='password'?'fixture-password':'']);
    (new OzekiMarkdownDocuments\Content\MarkdownSource())->store($id, '# ACCESS-'.$kind);
    if ($kind==='member') update_post_meta($id,'_wpmem_block','1');
    $ids[$kind]=$id;
}
function access_render(int $id, bool $embed): string {
    $saved = [$GLOBALS['wp_query'],$GLOBALS['wp_the_query'],$GLOBALS['post']??null];
    $q = new WP_Query(['p'=>$id,'post_type'=>'ozmd_document','post_status'=>'any']);
    $GLOBALS['wp_query']=$GLOBALS['wp_the_query']=$q;
    $q->is_singular=true;$q->is_single=true;$q->in_the_loop=true;
    $GLOBALS['post']=get_post($id);$q->queried_object=$GLOBALS['post'];$q->queried_object_id=$id;
    try { return $embed ? do_shortcode('[ozeki_markdown_document id="'.$id.'"]') : apply_filters('the_content', get_the_content(null,false,$id)); }
    finally { [$GLOBALS['wp_query'],$GLOBALS['wp_the_query'],$GLOBALS['post']]=$saved; }
}
$checks=[];
foreach ($ids as $kind=>$id) {
    foreach (['cold','warm'] as $cache) {
        delete_post_meta($id,'_ozmd_rendered_html');delete_post_meta($id,'_ozmd_render_hash');
        if ($cache==='warm') { wp_set_current_user(1); (new OzekiMarkdownDocuments\Rendering\CachedDocumentRenderer(new OzekiMarkdownDocuments\Rendering\MarkdownRenderer()))->render($id); }
        foreach ([0,$reader,1] as $uid) {
            wp_set_current_user($uid);
            $allowed=$kind==='public'||($kind==='member'&&$uid!==0)||(in_array($kind,['private','draft'],true)&&$uid===1);
            foreach ([false,true] as $embed) {
                if($cache==='cold'){delete_post_meta($id,'_ozmd_rendered_html');delete_post_meta($id,'_ozmd_render_hash');}
                $out=access_render($id,$embed);
                $checks[$kind.'.'.$cache.'.'.$uid.'.'.($embed?'embed':'direct')]=str_contains($out,'ACCESS-'.$kind)===$allowed;
                if (!$allowed && $cache==='cold') {
                    $checks[$kind.'.'.$uid.'.'.($embed?'embed':'direct').'.no_cache_readthrough']=get_post_meta($id,'_ozmd_rendered_html',true)==='';
                }
            }
        }
    }
}
// A previously denied member must not poison the next authorized render.
wp_set_current_user(0);access_render($ids['member'],true);wp_set_current_user($reader);
$checks['denied_then_authorized']=str_contains(access_render($ids['member'],true),'ACCESS-member');
wp_set_current_user(0);
require_once ABSPATH.WPINC.'/class-phpass.php';
$_COOKIE['wp-postpass_'.COOKIEHASH]=(new PasswordHash(8,true))->HashPassword('fixture-password');
$checks['password_entered_direct']=str_contains(access_render($ids['password'],false),'ACCESS-password');
$checks['password_entered_embed']=str_contains(access_render($ids['password'],true),'ACCESS-password');
unset($_COOKIE['wp-postpass_'.COOKIEHASH]);
$checks['password_removed_embed']=!str_contains(access_render($ids['password'],true),'ACCESS-password');
global $wpmem;
$oldBlock=$wpmem->block;
$wpmem->block['ozmd_document']=1;
$checks['default_member_condition']=!str_contains(access_render($ids['public'],true),'ACCESS-public');
update_post_meta($ids['public'],'_wpmem_block','0');
$checks['explicit_public_condition']=str_contains(access_render($ids['public'],true),'ACCESS-public');
$wpmem->block=$oldBlock;
wp_set_current_user($reader);
$deny=static fn($value)=>'';
add_filter('wpmem_securify',$deny,1000);
$checks['provider_denial']=!str_contains(access_render($ids['member'],true),'ACCESS-member');
$checks['provider_denial_public']=!str_contains(access_render($ids['public'],true),'ACCESS-public');
remove_filter('wpmem_securify',$deny,1000);
$oldExp=$wpmem->use_exp;$wpmem->use_exp=1;
$checks['unsupported_membership_denied']=!str_contains(access_render($ids['member'],true),'ACCESS-member');
$wpmem->use_exp=$oldExp;
echo wp_json_encode(['checks'=>$checks,'failed'=>array_keys(array_filter($checks,fn($v)=>!$v)),'pass'=>!in_array(false,$checks,true)]);
exit(in_array(false,$checks,true)?1:0);

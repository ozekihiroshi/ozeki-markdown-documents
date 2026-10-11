<?php
// Run in the disposable integration site, after activation, with wp eval-file.
if (!defined('ABSPATH')) { exit(1); }
require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
require_once ABSPATH . 'wp-admin/includes/screen.php';
$assert = static function ($ok, $message) { if (!$ok) { throw new RuntimeException($message); } };
$previousUser = get_current_user_id();
$previousScreen = get_current_screen();
wp_set_current_user(1);
$targets = [
    'OzekiMarkdownDocuments\\Admin\\DocumentEditor' => ['showNotice', 'ozmd_source_notice_'],
    'OzekiMarkdownDocuments\\Admin\\DocumentTransfer' => ['showImportNotice', 'ozmd_import_notice_'],
];
try {
    foreach ($targets as $class => [$method, $prefix]) {
        $object = null;
        foreach ($GLOBALS['wp_filter']['admin_notices']->callbacks as $callbacks) {
            foreach ($callbacks as $entry) {
                $fn = $entry['function'];
                if (is_array($fn) && is_object($fn[0]) && $fn[0] instanceof $class) { $object = $fn[0]; }
            }
        }
        $assert($object !== null, 'Notice handler missing');
        $key = $prefix . '1';
        $assert(get_transient($key) === false, 'Existing notice found; use an isolated site');
        $message = '<script>review-test</script>';
        $payload = $method === 'showNotice' ? $message : ['type'=>'success','message'=>$message,'url'=>''];
        set_transient($key, $payload, 60);
        try {
            foreach (['dashboard', 'post', 'plugins'] as $screen) {
                set_current_screen($screen);
                ob_start(); $object->$method(); $html = ob_get_clean();
                $assert($html === '' && get_transient($key) === $payload, 'Notice leaked or was consumed on unrelated screen');
            }
            set_current_screen('edit-ozmd_document');
            wp_set_current_user(0);
            ob_start(); $object->$method(); $html = ob_get_clean();
            $assert($html === '' && get_transient($key) === $payload, 'Notice leaked to another user');
            wp_set_current_user(1);
            ob_start(); $object->$method(); $html = ob_get_clean();
            $assert(str_contains($html, '&lt;script&gt;') && !str_contains($html, '<script>'), 'Notice escaping failed');
            $assert(str_contains($html, 'is-dismissible'), 'Notice is not dismissible');
            $assert(get_transient($key) === false, 'Notice was not consumed');
            ob_start(); $object->$method(); $html = ob_get_clean();
            $assert($html === '', 'Notice repeated');
        } finally { delete_transient($key); }
    }
    $assert(!method_exists('OzekiMarkdownDocuments\\Plugin', 'loadTextDomain'), 'Explicit translation loader remains');
    echo "review_notices=contextual_user_scoped_escaped_once\nresult=success\n";
} finally {
    wp_set_current_user($previousUser);
    $GLOBALS['current_screen'] = $previousScreen;
}

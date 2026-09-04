<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Admin;

use OzekiMarkdownDocuments\Content\DocumentMeta;
use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Content\MarkdownSource;

final class DocumentEditor
{
    private const NONCE_ACTION = 'ozmd_save_markdown_source';
    private const NONCE_NAME = 'ozmd_source_nonce';
    private const NOTICE_KEY_PREFIX = 'ozmd_source_notice_';

    public function __construct(
        private readonly DocumentMeta $meta,
        private readonly MarkdownSource $markdownSource,
        private readonly string $pluginFile
    ) {
    }

    public function registerHooks(): void
    {
        add_action('add_meta_boxes', [$this, 'addMetaBox']);
        add_action('save_post_' . DocumentPostType::POST_TYPE, [$this, 'save'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_notices', [$this, 'showNotice']);
    }

    public function addMetaBox(): void
    {
        add_meta_box(
            'ozmd-markdown-source',
            __('Markdown Source', 'ozeki-markdown-documents'),
            [$this, 'renderMetaBox'],
            DocumentPostType::POST_TYPE,
            'normal',
            'high'
        );
    }

    public function renderMetaBox(\WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        $source = $this->meta->source($post->ID);

        echo '<p>';
        echo esc_html__(
            'This Markdown text is the canonical document source. Rendered HTML can always be regenerated.',
            'ozeki-markdown-documents'
        );
        echo '</p>';
        echo '<textarea class="widefat code ozmd-source" name="ozmd_source" rows="28" spellcheck="false">';
        echo esc_textarea($source);
        echo '</textarea>';
    }

    public function save(int $postId, \WP_Post $post): void
    {
        if ($post->post_type !== DocumentPostType::POST_TYPE) {
            return;
        }

        if (wp_is_post_autosave($postId) || wp_is_post_revision($postId)) {
            return;
        }

        if (! isset($_POST[self::NONCE_NAME])) {
            return;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME]));
        if (! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        if (! isset($_POST['ozmd_source'])) {
            return;
        }

        $source = (string) wp_unslash($_POST['ozmd_source']);

        if (! $this->markdownSource->isValid($source)) {
            $this->setNotice(
                __('The Markdown source was not saved because it is not valid UTF-8 text or contains a NUL byte.', 'ozeki-markdown-documents')
            );
            return;
        }

        $this->markdownSource->store($postId, $source);
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if (! in_array($hookSuffix, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();
        if (! $screen instanceof \WP_Screen || $screen->post_type !== DocumentPostType::POST_TYPE) {
            return;
        }

        wp_enqueue_style(
            'ozmd-admin',
            plugins_url('assets/admin.css', $this->pluginFile),
            [],
            '0.1.0-dev'
        );
    }

    public function showNotice(): void
    {
        $userId = get_current_user_id();
        if ($userId < 1) {
            return;
        }

        $key = self::NOTICE_KEY_PREFIX . $userId;
        $message = get_transient($key);
        if (! is_string($message) || $message === '') {
            return;
        }

        delete_transient($key);
        echo '<div class="notice notice-error is-dismissible"><p>';
        echo esc_html($message);
        echo '</p></div>';
    }

    private function setNotice(string $message): void
    {
        $userId = get_current_user_id();
        if ($userId < 1) {
            return;
        }

        set_transient(self::NOTICE_KEY_PREFIX . $userId, $message, MINUTE_IN_SECONDS);
    }
}

<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Admin;

use OzekiMarkdownDocuments\Content\DocumentMeta;
use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Content\MarkdownSource;
use OzekiMarkdownDocuments\Frontend\MermaidAssets;
use OzekiMarkdownDocuments\Frontend\MathAssets;
use OzekiMarkdownDocuments\Rendering\MarkdownRenderer;

final class DocumentEditor
{
    private const NONCE_ACTION = 'ozmd_save_markdown_source';
    private const NONCE_NAME = 'ozmd_source_nonce';
    private const NOTICE_KEY_PREFIX = 'ozmd_source_notice_';
    private const PREVIEW_ACTION = 'ozmd_preview_markdown';
    private const PREVIEW_MAX_BYTES = 5 * MB_IN_BYTES;

    public function __construct(
        private readonly DocumentMeta $meta,
        private readonly MarkdownSource $markdownSource,
        private readonly MarkdownRenderer $renderer,
        private readonly MermaidAssets $mermaidAssets,
        private readonly MathAssets $mathAssets,
        private readonly string $pluginFile
    ) {
    }

    public function registerHooks(): void
    {
        add_action('add_meta_boxes', [$this, 'addMetaBox']);
        add_action('save_post_' . DocumentPostType::POST_TYPE, [$this, 'save'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_notices', [$this, 'showNotice']);
        add_action('wp_ajax_' . self::PREVIEW_ACTION, [$this, 'preview']);
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
        echo ' <a href="' . esc_url(
            admin_url(
                'edit.php?post_type=' . DocumentPostType::POST_TYPE
                . '&page=ozmd-getting-started'
            )
        ) . '">';
        echo esc_html__('Open the Markdown guide and examples', 'ozeki-markdown-documents');
        echo '</a>';
        echo '</p>';
        echo '<div class="ozmd-editor-grid">';
        echo '<section class="ozmd-editor-pane">';
        echo '<label class="ozmd-pane-heading" for="ozmd-source">';
        echo esc_html__('Markdown', 'ozeki-markdown-documents');
        echo '</label>';
        echo '<textarea id="ozmd-source" class="widefat code ozmd-source" name="ozmd_source" rows="28" spellcheck="false">';
        echo esc_textarea($source);
        echo '</textarea>';
        echo '</section>';
        echo '<section class="ozmd-preview-pane">';
        echo '<div class="ozmd-preview-heading">';
        echo '<span>' . esc_html__('Preview', 'ozeki-markdown-documents') . '</span>';
        echo '<span id="ozmd-preview-status" class="ozmd-preview-status" role="status" aria-live="polite"></span>';
        echo '</div>';
        echo '<div id="ozmd-preview" class="ozmd-preview" aria-live="polite"></div>';
        echo '</section>';
        echo '</div>';
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

        $postId = isset($_GET['post'])
            ? absint(wp_unslash($_GET['post']))
            : 0;

        $this->enqueuePreviewAssets($postId);
    }

    public function enqueuePreviewAssets(int $postId): void
    {
        wp_enqueue_style(
            'ozmd-admin',
            plugins_url('assets/admin.css', $this->pluginFile),
            [],
            '0.1.0-dev'
        );

        $this->mermaidAssets->enqueue();
        $this->mathAssets->enqueue();

        wp_enqueue_script(
            'ozmd-admin',
            plugins_url('assets/admin.js', $this->pluginFile),
            ['ozmd-mermaid-renderer', 'ozmd-math-renderer'],
            '0.1.0-dev',
            true
        );

        wp_localize_script(
            'ozmd-admin',
            'ozmdPreview',
            [
                'action' => self::PREVIEW_ACTION,
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(self::PREVIEW_ACTION),
                'postId' => $postId,
                'labels' => [
                    'loading' => __('Updating preview…', 'ozeki-markdown-documents'),
                    'ready' => __('Preview updated', 'ozeki-markdown-documents'),
                    'error' => __('Preview could not be updated.', 'ozeki-markdown-documents'),
                ],
            ]
        );
    }

    public function preview(): void
    {
        check_ajax_referer(self::PREVIEW_ACTION, 'nonce');

        $postId = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
        $allowed = $postId > 0
            ? current_user_can('edit_post', $postId)
            : current_user_can('edit_posts');

        if (! $allowed) {
            wp_send_json_error(
                ['message' => __('You are not allowed to preview this document.', 'ozeki-markdown-documents')],
                403
            );
        }

        $source = isset($_POST['source'])
            ? (string) wp_unslash($_POST['source'])
            : '';

        if (
            strlen($source) > self::PREVIEW_MAX_BYTES
            || ! $this->markdownSource->isValid($source)
        ) {
            wp_send_json_error(
                ['message' => __('Preview requires valid UTF-8 Markdown within the size limit.', 'ozeki-markdown-documents')],
                400
            );
        }

        try {
            $html = $this->renderer->render($source);
        } catch (\Throwable) {
            wp_send_json_error(
                ['message' => __('The Markdown preview could not be rendered.', 'ozeki-markdown-documents')],
                500
            );
        }

        wp_send_json_success(['html' => $html]);
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

<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Admin;

use OzekiMarkdownDocuments\Content\DocumentMeta;
use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Content\MarkdownDocumentImporter;

final class DocumentTransfer
{
    private const IMPORT_ACTION = 'ozmd_import_document';
    private const EXPORT_ACTION = 'ozmd_export_document';
    private const IMPORT_NOTICE_PREFIX = 'ozmd_import_notice_';
    private const MAX_IMPORT_BYTES = 5 * MB_IN_BYTES;

    public function __construct(private readonly MarkdownDocumentImporter $importer)
    {
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerImportPage']);
        add_action('admin_post_' . self::IMPORT_ACTION, [$this, 'handleImport']);
        add_action('admin_post_' . self::EXPORT_ACTION, [$this, 'handleExport']);
        add_filter('post_row_actions', [$this, 'addExportAction'], 10, 2);
        add_action('admin_notices', [$this, 'showImportNotice']);
    }

    public function registerImportPage(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . DocumentPostType::POST_TYPE,
            __('Import Markdown', 'ozeki-markdown-documents'),
            __('Import Markdown', 'ozeki-markdown-documents'),
            'edit_posts',
            'ozmd-import',
            [$this, 'renderImportPage']
        );
    }

    public function renderImportPage(): void
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('You are not allowed to import Markdown documents.', 'ozeki-markdown-documents'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Import Markdown', 'ozeki-markdown-documents') . '</h1>';
        echo '<p>';
        echo esc_html__(
            'Import one UTF-8 .md file as a new draft. The original Markdown remains the canonical source.',
            'ozeki-markdown-documents'
        );
        echo '</p>';
        echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="' . esc_attr(self::IMPORT_ACTION) . '">';
        wp_nonce_field(self::IMPORT_ACTION);
        echo '<input type="file" name="ozmd_file" accept=".md,.markdown,text/markdown,text/plain" required>';
        submit_button(__('Import as Draft', 'ozeki-markdown-documents'));
        echo '</form>';
        echo '<p>';
        printf(
            /* translators: %s: maximum Markdown import size. */
            esc_html__('Maximum file size: %s.', 'ozeki-markdown-documents'),
            esc_html(size_format(self::MAX_IMPORT_BYTES))
        );
        echo '</p>';
        echo '</div>';
    }

    public function handleImport(): void
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('You are not allowed to import Markdown documents.', 'ozeki-markdown-documents'));
        }

        check_admin_referer(self::IMPORT_ACTION);

        $upload = $_FILES['ozmd_file'] ?? null;
        if (! is_array($upload)) {
            $this->redirectWithNotice(
                'error',
                __('No Markdown file was uploaded.', 'ozeki-markdown-documents')
            );
        }

        $error = isset($upload['error']) ? (int) $upload['error'] : UPLOAD_ERR_NO_FILE;
        $size = isset($upload['size']) ? (int) $upload['size'] : 0;
        $temporaryPath = isset($upload['tmp_name']) ? (string) $upload['tmp_name'] : '';
        $filename = isset($upload['name']) ? (string) $upload['name'] : '';

        if ($error !== UPLOAD_ERR_OK) {
            $this->redirectWithNotice(
                'error',
                __('The Markdown upload did not complete successfully.', 'ozeki-markdown-documents')
            );
        }

        if ($size < 0 || $size > self::MAX_IMPORT_BYTES) {
            $this->redirectWithNotice(
                'error',
                __('The Markdown file exceeds the import size limit.', 'ozeki-markdown-documents')
            );
        }

        $source = $this->readUploadedFile($temporaryPath);
        if (is_wp_error($source)) {
            $this->redirectWithNotice('error', $source->get_error_message());
        }

        $postId = $this->importer->createDraft($filename, $source);
        if (is_wp_error($postId)) {
            $this->redirectWithNotice('error', $postId->get_error_message());
        }

        $this->redirectWithNotice(
            'success',
            __('Markdown imported as a draft.', 'ozeki-markdown-documents'),
            get_edit_post_link($postId, 'raw') ?: ''
        );
    }

    /**
     * @param array<string, string> $actions
     * @return array<string, string>
     */
    public function addExportAction(array $actions, \WP_Post $post): array
    {
        if (
            $post->post_type !== DocumentPostType::POST_TYPE
            || ! current_user_can('edit_post', $post->ID)
        ) {
            return $actions;
        }

        $url = wp_nonce_url(
            add_query_arg(
                [
                    'action' => self::EXPORT_ACTION,
                    'post_id' => $post->ID,
                ],
                admin_url('admin-post.php')
            ),
            self::EXPORT_ACTION . '_' . $post->ID
        );

        $actions['ozmd_export'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url($url),
            esc_html__('Export .md', 'ozeki-markdown-documents')
        );

        return $actions;
    }

    public function handleExport(): void
    {
        $postId = isset($_GET['post_id']) ? absint(wp_unslash($_GET['post_id'])) : 0;
        $post = get_post($postId);

        if (
            ! $post instanceof \WP_Post
            || $post->post_type !== DocumentPostType::POST_TYPE
            || ! current_user_can('edit_post', $postId)
        ) {
            wp_die(esc_html__('You are not allowed to export this Markdown document.', 'ozeki-markdown-documents'));
        }

        check_admin_referer(self::EXPORT_ACTION . '_' . $postId);

        $source = (string) get_post_meta($postId, DocumentMeta::SOURCE, true);
        $filename = $this->exportFilename($post);

        nocache_headers();
        header('Content-Type: text/markdown; charset=UTF-8');
        header('Content-Disposition: attachment; filename="document.md"; filename*=UTF-8\'\'' . rawurlencode($filename));
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . strlen($source));

        // The exact canonical Markdown bytes are intentionally returned without HTML escaping.
        echo $source; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    public function showImportNotice(): void
    {
        $userId = get_current_user_id();
        if ($userId < 1) {
            return;
        }

        $key = self::IMPORT_NOTICE_PREFIX . $userId;
        $notice = get_transient($key);
        if (! is_array($notice)) {
            return;
        }

        delete_transient($key);
        $type = ($notice['type'] ?? '') === 'success' ? 'success' : 'error';
        $message = isset($notice['message']) ? (string) $notice['message'] : '';
        $url = isset($notice['url']) ? (string) $notice['url'] : '';

        echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>';
        echo esc_html($message);
        if ($url !== '') {
            echo ' <a href="' . esc_url($url) . '">';
            echo esc_html__('Edit imported document', 'ozeki-markdown-documents');
            echo '</a>';
        }
        echo '</p></div>';
    }

    /**
     * @return string|\WP_Error
     */
    private function readUploadedFile(string $path)
    {
        if ($path === '' || ! is_uploaded_file($path)) {
            return new \WP_Error(
                'ozmd_invalid_upload',
                __('The uploaded Markdown file could not be verified.', 'ozeki-markdown-documents')
            );
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return new \WP_Error(
                'ozmd_upload_open_failed',
                __('The uploaded Markdown file could not be opened.', 'ozeki-markdown-documents')
            );
        }

        $source = '';

        try {
            while (! feof($handle)) {
                $chunk = fread($handle, 8192);
                if ($chunk === false) {
                    return new \WP_Error(
                        'ozmd_upload_read_failed',
                        __('The uploaded Markdown file could not be read completely.', 'ozeki-markdown-documents')
                    );
                }

                $source .= $chunk;
                if (strlen($source) > self::MAX_IMPORT_BYTES) {
                    return new \WP_Error(
                        'ozmd_upload_too_large',
                        __('The Markdown file exceeds the import size limit.', 'ozeki-markdown-documents')
                    );
                }
            }
        } finally {
            fclose($handle);
        }

        return $source;
    }

    private function exportFilename(\WP_Post $post): string
    {
        $filename = sanitize_file_name(
            (string) get_post_meta($post->ID, DocumentMeta::ORIGINAL_FILENAME, true)
        );

        if (strtolower((string) pathinfo($filename, PATHINFO_EXTENSION)) !== 'md') {
            $filename = sanitize_file_name($post->post_name ?: $post->post_title);
            $filename = ($filename !== '' ? $filename : 'document') . '.md';
        }

        return $filename;
    }

    private function redirectWithNotice(
        string $type,
        string $message,
        string $url = ''
    ): void {
        $userId = get_current_user_id();
        if ($userId > 0) {
            set_transient(
                self::IMPORT_NOTICE_PREFIX . $userId,
                [
                    'type' => $type,
                    'message' => $message,
                    'url' => $url,
                ],
                MINUTE_IN_SECONDS
            );
        }

        wp_safe_redirect(
            admin_url('edit.php?post_type=' . DocumentPostType::POST_TYPE)
        );
        exit;
    }
}

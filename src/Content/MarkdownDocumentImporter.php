<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Content;

final class MarkdownDocumentImporter
{
    public function __construct(private readonly MarkdownSource $markdownSource)
    {
    }

    /**
     * @return int|\WP_Error
     */
    public function createDraft(string $filename, string $source)
    {
        $filename = sanitize_file_name(wp_basename($filename));

        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

        if ($filename === '' || ! in_array($extension, ['md', 'markdown'], true)) {
            return new \WP_Error(
                'ozmd_invalid_extension',
                __('Choose a file with the .md or .markdown extension.', 'ozeki-markdown-documents')
            );
        }

        if (! $this->markdownSource->isValid($source)) {
            return new \WP_Error(
                'ozmd_invalid_markdown_source',
                __('The Markdown file must be valid UTF-8 text without NUL bytes.', 'ozeki-markdown-documents')
            );
        }

        $title = (string) pathinfo($filename, PATHINFO_FILENAME);
        $title = trim((string) preg_replace('/[-_]+/', ' ', $title));

        if ($title === '') {
            $title = __('Imported Markdown Document', 'ozeki-markdown-documents');
        }

        $postId = wp_insert_post(
            [
                'post_type' => DocumentPostType::POST_TYPE,
                'post_status' => 'draft',
                'post_title' => $title,
            ],
            true
        );

        if (is_wp_error($postId)) {
            return $postId;
        }

        $this->markdownSource->store($postId, $source);
        update_post_meta($postId, DocumentMeta::ORIGINAL_FILENAME, $filename);

        return $postId;
    }
}

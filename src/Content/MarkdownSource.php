<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Content;

final class MarkdownSource
{
    public function isValid(string $source): bool
    {
        if (str_contains($source, "\0")) {
            return false;
        }

        if ($source === '') {
            return true;
        }

        return wp_check_invalid_utf8($source, false) !== '';
    }

    public function store(int $postId, string $source): void
    {
        update_post_meta($postId, DocumentMeta::SOURCE, $source);
        update_post_meta($postId, DocumentMeta::SOURCE_SHA256, hash('sha256', $source));
        delete_post_meta($postId, DocumentMeta::RENDERED_HTML);
        delete_post_meta($postId, DocumentMeta::RENDER_HASH);
    }
}

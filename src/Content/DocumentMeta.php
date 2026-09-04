<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Content;

final class DocumentMeta
{
    public const SOURCE = '_ozmd_source';
    public const SOURCE_SHA256 = '_ozmd_source_sha256';
    public const ORIGINAL_FILENAME = '_ozmd_original_filename';
    public const RENDERED_HTML = '_ozmd_rendered_html';
    public const RENDER_HASH = '_ozmd_render_hash';

    public function register(): void
    {
        register_post_meta(
            DocumentPostType::POST_TYPE,
            self::SOURCE,
            [
                'type' => 'string',
                'single' => true,
                'show_in_rest' => false,
                'revisions_enabled' => true,
                'auth_callback' => [$this, 'canEdit'],
            ]
        );

        foreach (
            [
                self::SOURCE_SHA256,
                self::ORIGINAL_FILENAME,
                self::RENDERED_HTML,
                self::RENDER_HASH,
            ] as $key
        ) {
            register_post_meta(
                DocumentPostType::POST_TYPE,
                $key,
                [
                    'type' => 'string',
                    'single' => true,
                    'show_in_rest' => false,
                    'auth_callback' => [$this, 'canEdit'],
                ]
            );
        }
    }

    public function canEdit(
        bool $allowed,
        string $metaKey,
        int $postId
    ): bool {
        unset($allowed, $metaKey);

        return current_user_can('edit_post', $postId);
    }

    public function source(int $postId): string
    {
        return (string) get_post_meta($postId, self::SOURCE, true);
    }
}

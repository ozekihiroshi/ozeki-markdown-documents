<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Content;

final class DocumentPostType
{
    public const POST_TYPE = 'ozmd_document';

    public function register(): void
    {
        register_post_type(
            self::POST_TYPE,
            [
                'labels' => [
                    'name' => __('Markdown Documents', 'ozeki-markdown-documents'),
                    'singular_name' => __('Markdown Document', 'ozeki-markdown-documents'),
                    'add_new_item' => __('Add New Markdown Document', 'ozeki-markdown-documents'),
                    'edit_item' => __('Edit Markdown Document', 'ozeki-markdown-documents'),
                    'new_item' => __('New Markdown Document', 'ozeki-markdown-documents'),
                    'view_item' => __('View Markdown Document', 'ozeki-markdown-documents'),
                    'search_items' => __('Search Markdown Documents', 'ozeki-markdown-documents'),
                    'not_found' => __('No Markdown documents found.', 'ozeki-markdown-documents'),
                    'not_found_in_trash' => __('No Markdown documents found in Trash.', 'ozeki-markdown-documents'),
                ],
                'public' => true,
                'show_in_rest' => false,
                'has_archive' => true,
                'rewrite' => ['slug' => 'markdown-documents'],
                'menu_icon' => 'dashicons-media-code',
                'capability_type' => ['post', 'posts'],
                'map_meta_cap' => true,
                'supports' => ['title', 'author', 'revisions'],
                'can_export' => true,
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Frontend;

use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Rendering\CachedDocumentRenderer;

final class DocumentShortcode
{
    public const TAG = 'ozeki_markdown_document';

    public function __construct(private readonly CachedDocumentRenderer $renderer)
    {
    }

    /**
     * @param array<string, mixed>|string $attributes
     */
    public function render($attributes): string
    {
        $attributes = shortcode_atts(['id' => 0], $attributes, self::TAG);
        $postId = absint($attributes['id']);
        if ($postId < 1) {
            return '';
        }

        $post = get_post($postId);
        if (! $post instanceof \WP_Post || $post->post_type !== DocumentPostType::POST_TYPE) {
            return '';
        }

        if ($post->post_status !== 'publish' && ! current_user_can('read_post', $postId)) {
            return '';
        }

        $rendered = $this->renderer->render($postId);
        if (is_wp_error($rendered)) {
            return DocumentMarkup::renderError($rendered);
        }

        return DocumentMarkup::wrap($postId, $rendered);
    }
}

<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Frontend;

use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Rendering\CachedDocumentRenderer;

final class DocumentContentFilter
{
    public function __construct(
        private readonly CachedDocumentRenderer $renderer,
        private readonly MermaidAssets $mermaidAssets,
        private readonly MathAssets $mathAssets
    ) {
    }

    public function filter(string $content): string
    {
        if (
            ! is_singular(DocumentPostType::POST_TYPE)
            || ! in_the_loop()
            || ! is_main_query()
        ) {
            return $content;
        }

        $postId = get_the_ID();
        if ($postId < 1) {
            return $content;
        }

        $document = get_post($postId);
        if (!$document instanceof \WP_Post) {
            return $content;
        }
        $denial = DocumentAccess::denial($document);
        if ($denial !== null) {
            // Keep WordPress's ordinary password-entry form on the direct page.
            return $denial === 'password' ? get_the_password_form($document) : DocumentAccess::message($denial);
        }

        $rendered = $this->renderer->render($postId);
        if (is_wp_error($rendered)) {
            return DocumentMarkup::renderError($rendered);
        }

        $this->mermaidAssets->enqueueForHtml($rendered);
        $this->mathAssets->enqueueForHtml($rendered);

        return DocumentMarkup::wrap($postId, $rendered);
    }
}

<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Frontend;

use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Rendering\CachedDocumentRenderer;

final class DocumentContentFilter
{
    public function __construct(
        private readonly CachedDocumentRenderer $renderer,
        private readonly MermaidAssets $mermaidAssets
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

        $rendered = $this->renderer->render($postId);
        if (is_wp_error($rendered)) {
            return DocumentMarkup::renderError($rendered);
        }

        $this->mermaidAssets->enqueueForHtml($rendered);

        return DocumentMarkup::wrap($postId, $rendered);
    }
}

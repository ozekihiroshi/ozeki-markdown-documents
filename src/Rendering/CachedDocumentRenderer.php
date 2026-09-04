<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Rendering;

use OzekiMarkdownDocuments\Content\DocumentMeta;
use OzekiMarkdownDocuments\Plugin;

final class CachedDocumentRenderer
{
    public function __construct(private readonly MarkdownRenderer $renderer)
    {
    }

    /**
     * @return string|\WP_Error
     */
    public function render(int $postId)
    {
        $source = (string) get_post_meta($postId, DocumentMeta::SOURCE, true);
        $expectedHash = $this->renderHash($source);
        $cachedHash = (string) get_post_meta($postId, DocumentMeta::RENDER_HASH, true);
        $cachedHtml = (string) get_post_meta($postId, DocumentMeta::RENDERED_HTML, true);

        if ($cachedHash === $expectedHash) {
            return $cachedHtml;
        }

        try {
            $renderedHtml = $this->renderer->render($source);
        } catch (\Throwable $exception) {
            return new \WP_Error(
                'ozmd_render_failed',
                __('The Markdown document could not be rendered.', 'ozeki-markdown-documents'),
                $exception
            );
        }

        update_post_meta(
            $postId,
            DocumentMeta::RENDERED_HTML,
            wp_slash($renderedHtml)
        );
        update_post_meta($postId, DocumentMeta::RENDER_HASH, $expectedHash);

        return $renderedHtml;
    }

    private function renderHash(string $source): string
    {
        return hash(
            'sha256',
            implode(
                "\n",
                [
                    $source,
                    Plugin::VERSION,
                    MarkdownRenderer::SCHEMA_VERSION,
                    'commonmark-gfm:html-input=escape:unsafe-links=false',
                ]
            )
        );
    }
}

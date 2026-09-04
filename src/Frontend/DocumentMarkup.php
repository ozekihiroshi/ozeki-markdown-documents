<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Frontend;

final class DocumentMarkup
{
    public static function wrap(int $postId, string $html): string
    {
        return sprintf(
            '<article class="ozmd-document" data-ozmd-document-id="%1$d">%2$s</article>',
            $postId,
            $html
        );
    }

    public static function renderError(\WP_Error $error): string
    {
        return sprintf(
            '<p class="ozmd-render-error">%s</p>',
            esc_html($error->get_error_message())
        );
    }
}

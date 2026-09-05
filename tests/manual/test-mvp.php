<?php

use OzekiMarkdownDocuments\Content\DocumentMeta;
use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Content\ExampleDocument;
use OzekiMarkdownDocuments\Content\MarkdownDocumentImporter;
use OzekiMarkdownDocuments\Content\MarkdownSource;
use OzekiMarkdownDocuments\Frontend\DocumentShortcode;
use OzekiMarkdownDocuments\Frontend\MermaidAssets;
use OzekiMarkdownDocuments\Frontend\MathAssets;
use OzekiMarkdownDocuments\Rendering\CachedDocumentRenderer;
use OzekiMarkdownDocuments\Rendering\MarkdownRenderer;

if (! defined('ABSPATH')) {
    fwrite(STDERR, "Run this file with wp eval-file.\n");
    exit(1);
}

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException($message);
    }
};

$sourceV1 = <<<'MARKDOWN'
# Portable Markdown

This is **canonical** source with ~~GFM~~.

| Format | Stored as |
| --- | --- |
| Markdown | UTF-8 text |

<script>alert('unsafe');</script>

[unsafe link](javascript:alert('unsafe'))

~~~mermaid
flowchart LR
    A[Markdown] --> B[Diagram]
~~~

Inline formula: `asciimath:a/b`.

~~~math
\frac{x + 1}{y}
~~~
MARKDOWN;

$sourceV2 = <<<'MARKDOWN'
# Portable Markdown, revised

The second version remains reusable.
MARKDOWN;

$importSource = "# Imported UTF-8\r\n\r\n日本語と改行をそのまま保持します。\r\n";
$importedPostId = 0;

$postId = wp_insert_post(
    [
        'post_type' => DocumentPostType::POST_TYPE,
        'post_status' => 'publish',
        'post_title' => 'MVP verification document',
    ],
    true
);

if (is_wp_error($postId)) {
    throw new RuntimeException($postId->get_error_message());
}

try {
    $markdownSource = new MarkdownSource();
    $importer = new MarkdownDocumentImporter($markdownSource);
    $importedPostId = $importer->createDraft('portable-document.md', $importSource);
    $assert(is_int($importedPostId), 'Markdown import returned an error.');
    $assert(
        get_post_meta($importedPostId, DocumentMeta::SOURCE, true) === $importSource,
        'Markdown import did not preserve the exact source bytes.'
    );
    $assert(
        get_post_meta($importedPostId, DocumentMeta::ORIGINAL_FILENAME, true) === 'portable-document.md',
        'Markdown import did not preserve the source filename.'
    );

    $markdownSource->store($postId, $sourceV1);
    $assert(
        get_post_meta($postId, DocumentMeta::SOURCE, true) === $sourceV1,
        'Markdown storage did not preserve LaTeX backslashes exactly.'
    );
    wp_update_post(['ID' => $postId, 'post_title' => 'MVP verification document v1']);

    $renderer = new CachedDocumentRenderer(new MarkdownRenderer());
    $exampleSource = ExampleDocument::source();
    $exampleHtml = (new MarkdownRenderer())->render($exampleSource);
    $assert(
        str_contains($exampleHtml, '<h1>A Well-Structured Markdown Example</h1>'),
        'The built-in guide example could not be rendered.'
    );
    $assert(
        str_contains($exampleHtml, 'class="language-mermaid"')
        && str_contains($exampleHtml, 'class="language-asciimath"')
        && str_contains($exampleHtml, 'class="language-math"'),
        'The built-in guide does not cover diagrams and both math formats.'
    );
    $mermaidGuideHtml = (new MarkdownRenderer())->render(
        ExampleDocument::guide(ExampleDocument::MERMAID)['source']
    );
    $mathGuideHtml = (new MarkdownRenderer())->render(
        ExampleDocument::guide(ExampleDocument::MATH)['source']
    );
    $assert(
        substr_count($mermaidGuideHtml, 'class="language-mermaid"') === 8,
        'The Mermaid guide does not contain the expected diagram library.'
    );
    $assert(
        substr_count($mathGuideHtml, 'class="language-math"') >= 10
        && substr_count($mathGuideHtml, 'class="language-asciimath"') >= 10,
        'The math guide does not contain the expected AsciiMath and LaTeX library.'
    );
    $mathGuideSource = ExampleDocument::guide(ExampleDocument::MATH)['source'];
    $assert(
        str_contains($mathGuideSource, 'vec(v) = (v_1;v_2;v_3)')
        && str_contains($mathGuideSource, 'A=\\begin{bmatrix}')
        && str_contains($mathGuideSource, 'lim_(x->0) (sin x)/x = 1')
        && str_contains($mathGuideSource, 'abs(x) = {x if x >= 0;'),
        'Paired AsciiMath and LaTeX guide examples are not notation-aligned.'
    );
    $html = $renderer->render($postId);
    $assert(is_string($html), 'Rendering returned an error.');
    $assert(str_contains($html, '<h1>Portable Markdown</h1>'), 'Heading was not rendered.');
    $assert(str_contains($html, '<table>'), 'GFM table was not rendered.');
    $assert(str_contains($html, '<del>GFM</del>'), 'GFM strikethrough was not rendered.');
    $assert(
        str_contains($html, 'class="language-mermaid"'),
        'Mermaid fenced code was not preserved for browser rendering.'
    );
    $assert(
        str_contains($html, '<code>asciimath:a/b</code>'),
        'Inline AsciiMath was not preserved for browser rendering.'
    );
    $assert(
        str_contains($html, 'class="language-math"'),
        'LaTeX fenced code was not preserved for browser rendering.'
    );
    $assert(! str_contains($html, '<script'), 'Raw script HTML was not neutralized.');
    $assert(! str_contains($html, 'href="javascript:'), 'Unsafe link was not neutralized.');

    $cachedHtml = (string) get_post_meta($postId, DocumentMeta::RENDERED_HTML, true);
    $renderHash = (string) get_post_meta($postId, DocumentMeta::RENDER_HASH, true);
    $assert($cachedHtml === $html, 'Rendered HTML cache does not match.');
    $assert(strlen($renderHash) === 64, 'Render hash was not stored.');
    $assert($renderer->render($postId) === $html, 'Cached render changed the output.');

    $mermaidAssets = new MermaidAssets(
        WP_PLUGIN_DIR . '/ozeki-markdown-documents/ozeki-markdown-documents.php'
    );
    $mathAssets = new MathAssets(
        WP_PLUGIN_DIR . '/ozeki-markdown-documents/ozeki-markdown-documents.php'
    );
    $shortcode = new DocumentShortcode($renderer, $mermaidAssets, $mathAssets);
    $shortcodeHtml = $shortcode->render(['id' => $postId]);
    $assert(str_contains($shortcodeHtml, 'ozmd-document'), 'Shortcode wrapper is missing.');
    $assert(str_contains($shortcodeHtml, 'Portable Markdown'), 'Shortcode content is missing.');
    $assert(
        wp_script_is('ozmd-mermaid-renderer', 'enqueued'),
        'Mermaid assets were not conditionally enqueued.'
    );
    $assert(
        wp_script_is('ozmd-math-renderer', 'enqueued'),
        'Math assets were not conditionally enqueued.'
    );

    $revisionV1Id = _wp_put_post_revision($postId);
    $assert(
        is_int($revisionV1Id) && $revisionV1Id > 0,
        'The first Markdown revision could not be created explicitly.'
    );
    wp_save_revisioned_meta_fields($revisionV1Id, $postId);
    $assert(
        get_metadata('post', $revisionV1Id, DocumentMeta::SOURCE, true) === $sourceV1,
        'The explicit revision did not copy the Markdown source.'
    );

    $markdownSource->store($postId, $sourceV2);
    wp_update_post(['ID' => $postId, 'post_title' => 'MVP verification document v2']);

    $revisionWithV1 = get_post($revisionV1Id);
    $assert($revisionWithV1 instanceof WP_Post, 'No revision contains the first Markdown source.');
    $restored = wp_restore_post_revision($revisionWithV1->ID);
    $assert($restored === $postId, 'Revision restore failed.');
    $assert(
        get_post_meta($postId, DocumentMeta::SOURCE, true) === $sourceV1,
        'Revision restore did not restore the Markdown source.'
    );

    unload_textdomain('ozeki-markdown-documents');
    $japaneseCatalog = WP_PLUGIN_DIR
        . '/ozeki-markdown-documents/languages/ozeki-markdown-documents-ja.mo';
    $assert(
        load_textdomain('ozeki-markdown-documents', $japaneseCatalog, 'ja_JP'),
        'The bundled Japanese translation catalog could not be loaded.'
    );
    $assert(
        __('Markdown Guide', 'ozeki-markdown-documents') === 'Markdownガイド',
        'The bundled Japanese translation was not applied.'
    );

    echo 'post_type=registered' . PHP_EOL;
    echo 'renderer=commonmark_gfm_safe' . PHP_EOL;
    echo 'cache=verified' . PHP_EOL;
    echo 'shortcode=verified' . PHP_EOL;
    echo 'mermaid_assets=conditional' . PHP_EOL;
    echo 'math_assets=conditional' . PHP_EOL;
    echo 'latex_backslashes=exact_bytes_preserved' . PHP_EOL;
    echo 'meta_revision=restored' . PHP_EOL;
    echo 'md_import=exact_bytes_preserved' . PHP_EOL;
    echo 'guide_example=rendered_without_persistence' . PHP_EOL;
    echo 'specialized_guides=mermaid_and_math_rendered' . PHP_EOL;
    echo 'japanese_translation=loaded' . PHP_EOL;
    echo 'result=success' . PHP_EOL;
} finally {
    wp_delete_post($postId, true);
    if (is_int($importedPostId) && $importedPostId > 0) {
        wp_delete_post($importedPostId, true);
    }
}

<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use League\CommonMark\GithubFlavoredMarkdownConverter;

// Exercise upstream GFM defaults too: the plugin additionally escapes raw HTML
// and filters generated output through WordPress's HTML allow-list.
$converter = new GithubFlavoredMarkdownConverter();
foreach (['script', 'iframe', 'style'] as $tag) {
    $source = "<div>\n<$tag\n\n<span src=\"/security-regression.js\">\n";
    $html = (string) $converter->convert($source);
    if (str_contains($html, "<$tag")) {
        throw new RuntimeException("Unterminated disallowed tag was emitted: $tag");
    }
}

// A large digit-leading paragraph used to trigger a quadratic GFM table scan.
// The generous time budget catches severe regressions, not small timing changes.
$lineCount = 300000;
$source = str_repeat("12345678\n", $lineCount);
$started = hrtime(true);
$html = (string) $converter->convert($source);
$seconds = (hrtime(true) - $started) / 1e9;
if (substr_count($html, '12345678') !== $lineCount || $seconds > 20) {
    throw new RuntimeException("Large paragraph regression: {$seconds} seconds");
}
echo "CommonMark security regressions passed ({$seconds} seconds).\n";

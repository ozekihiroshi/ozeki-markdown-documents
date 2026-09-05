<?php

declare(strict_types=1);

if ($argc !== 2) {
    fwrite(STDERR, "Usage: php test-release-package.php /path/to/plugin\n");
    exit(2);
}

$pluginDirectory = rtrim($argv[1], DIRECTORY_SEPARATOR);
$autoload = $pluginDirectory . '/vendor/autoload.php';

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
};

$requiredFiles = [
    'ozeki-markdown-documents.php',
    'uninstall.php',
    'readme.txt',
    'LICENSE',
    'third-party-notices.txt',
    'composer.json',
    'package.json',
    'package-lock.json',
    'vendor/autoload.php',
    'assets/admin.css',
    'assets/admin.js',
    'assets/frontend.css',
    'assets/math-render-source.js',
    'assets/math-render.js',
    'assets/mermaid-render.js',
    'assets/vendor/mermaid/LICENSE',
    'assets/vendor/katex/LICENSE',
    'assets/vendor/asciimath-parser/LICENSE',
    'languages/ozeki-markdown-documents.pot',
    'languages/ozeki-markdown-documents-ja.po',
    'languages/ozeki-markdown-documents-ja.mo',
];

foreach ($requiredFiles as $file) {
    $assert(is_file($pluginDirectory . '/' . $file), 'Missing release file: ' . $file);
}

$forbiddenPaths = [
    '.git',
    '.github',
    'build',
    'docs',
    'node_modules',
    'tests',
    'tools',
    'composer.lock',
    'scoper.inc.php',
];

foreach ($forbiddenPaths as $path) {
    $assert(! file_exists($pluginDirectory . '/' . $path), 'Development path leaked into release: ' . $path);
}

require $autoload;

$assert(
    class_exists('OzekiMarkdownDocuments\\Plugin'),
    'Plugin class is not autoloadable from the release.'
);
$assert(
    class_exists('OzekiMarkdownDocumentsVendor\\League\\CommonMark\\MarkdownConverter'),
    'Scoped CommonMark class is not autoloadable from the release.'
);
$assert(
    ! class_exists('League\\CommonMark\\MarkdownConverter'),
    'An unscoped CommonMark class remains autoloadable from the release.'
);

$documentEditorSource = file_get_contents($pluginDirectory . '/src/Admin/DocumentEditor.php');
$assert(is_string($documentEditorSource), 'The release DocumentEditor could not be read.');
$assert(
    ! str_contains($documentEditorSource, 'OzekiMarkdownDocumentsVendor\\WP_'),
    'A WordPress core class was incorrectly scoped in first-party code.'
);
$assert(
    ! str_contains($documentEditorSource, '0.1.0-dev'),
    'A development asset version remains in the release.'
);

fwrite(STDOUT, "Release package structure and dependency isolation passed.\n");

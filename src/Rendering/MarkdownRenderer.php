<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Rendering;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

final class MarkdownRenderer
{
    public const SCHEMA_VERSION = '1';

    private readonly MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment(
            [
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
                'max_nesting_level' => 100,
            ]
        );
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $this->converter = new MarkdownConverter($environment);
    }

    public function render(string $source): string
    {
        $html = (string) $this->converter->convert($source);

        return wp_kses_post($html);
    }
}

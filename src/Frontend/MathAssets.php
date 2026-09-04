<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Frontend;

use OzekiMarkdownDocuments\Plugin;

final class MathAssets
{
    public const KATEX_VERSION = '0.18.5';
    public const ASCIIMATH_VERSION = '0.6.11';

    private bool $configured = false;

    public function __construct(private readonly string $pluginFile)
    {
    }

    public function registerHooks(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'register']);
    }

    public function register(): void
    {
        wp_register_script(
            'ozmd-math-renderer',
            plugins_url('assets/math-render.js', $this->pluginFile),
            [],
            Plugin::VERSION,
            true
        );
    }

    public function enqueue(): void
    {
        if (! wp_script_is('ozmd-math-renderer', 'registered')) {
            $this->register();
        }

        if (! $this->configured) {
            wp_add_inline_script(
                'ozmd-math-renderer',
                'window.ozmdMathConfig = ' . wp_json_encode(
                    [
                        'katexCssUrl' => plugins_url(
                            'assets/vendor/katex/katex.min.css',
                            $this->pluginFile
                        ),
                        'labels' => [
                            'copy' => __('Copy LaTeX', 'ozeki-markdown-documents'),
                            'copied' => __('Copied', 'ozeki-markdown-documents'),
                            'copyFailed' => __('Copy failed', 'ozeki-markdown-documents'),
                        ],
                    ],
                    JSON_UNESCAPED_SLASHES
                ) . ';',
                'before'
            );
            $this->configured = true;
        }

        wp_enqueue_script('ozmd-math-renderer');
    }

    public function enqueueForHtml(string $html): void
    {
        foreach (
            [
                'language-math',
                'language-latex',
                'language-asciimath',
                '>math:',
                '>latex:',
                '>asciimath:',
            ] as $marker
        ) {
            if (str_contains($html, $marker)) {
                $this->enqueue();
                return;
            }
        }
    }
}

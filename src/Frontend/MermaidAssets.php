<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Frontend;

use OzekiMarkdownDocuments\Plugin;

final class MermaidAssets
{
    public const MERMAID_VERSION = '11.17.2';

    public function __construct(private readonly string $pluginFile)
    {
    }

    public function registerHooks(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'registerFrontend']);
    }

    public function registerFrontend(): void
    {
        $this->register();
        wp_enqueue_style('ozmd-frontend');
    }

    public function register(): void
    {
        wp_register_style(
            'ozmd-frontend',
            plugins_url('assets/frontend.css', $this->pluginFile),
            [],
            Plugin::VERSION
        );

        wp_register_script(
            'ozmd-mermaid',
            plugins_url(
                'assets/vendor/mermaid/mermaid.min.js',
                $this->pluginFile
            ),
            [],
            self::MERMAID_VERSION,
            true
        );

        wp_register_script(
            'ozmd-mermaid-renderer',
            plugins_url('assets/mermaid-render.js', $this->pluginFile),
            ['ozmd-mermaid'],
            Plugin::VERSION,
            true
        );
    }

    public function enqueue(): void
    {
        if (! wp_script_is('ozmd-mermaid-renderer', 'registered')) {
            $this->register();
        }

        wp_enqueue_script('ozmd-mermaid-renderer');
    }

    public function enqueueForHtml(string $html): void
    {
        if (! str_contains($html, 'language-mermaid')) {
            return;
        }

        $this->enqueue();
    }
}

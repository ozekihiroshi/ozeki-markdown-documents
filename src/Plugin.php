<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments;

use OzekiMarkdownDocuments\Admin\DocumentEditor;
use OzekiMarkdownDocuments\Admin\DocumentTransfer;
use OzekiMarkdownDocuments\Admin\GettingStartedPage;
use OzekiMarkdownDocuments\Content\DocumentMeta;
use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Content\MarkdownDocumentImporter;
use OzekiMarkdownDocuments\Content\MarkdownSource;
use OzekiMarkdownDocuments\Frontend\DocumentContentFilter;
use OzekiMarkdownDocuments\Frontend\DocumentShortcode;
use OzekiMarkdownDocuments\Frontend\MermaidAssets;
use OzekiMarkdownDocuments\Frontend\MathAssets;
use OzekiMarkdownDocuments\Rendering\CachedDocumentRenderer;
use OzekiMarkdownDocuments\Rendering\MarkdownRenderer;

final class Plugin
{
    public const VERSION = '0.1.0';

    private static ?self $instance = null;

    private function __construct(private readonly string $pluginFile)
    {
    }

    public static function boot(string $pluginFile): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        self::$instance = new self($pluginFile);
        self::$instance->registerHooks();

        return self::$instance;
    }

    private function registerHooks(): void
    {
        add_action('init', [$this, 'loadTextDomain'], 0);
        $postType = new DocumentPostType();
        $meta = new DocumentMeta();
        $markdownSource = new MarkdownSource();
        $markdownRenderer = new MarkdownRenderer();
        $mermaidAssets = new MermaidAssets($this->pluginFile);
        $mathAssets = new MathAssets($this->pluginFile);
        $editor = new DocumentEditor(
            $meta,
            $markdownSource,
            $markdownRenderer,
            $mermaidAssets,
            $mathAssets,
            $this->pluginFile
        );
        $transfer = new DocumentTransfer(
            new MarkdownDocumentImporter($markdownSource)
        );
        $gettingStarted = new GettingStartedPage(
            $editor,
            new MarkdownDocumentImporter($markdownSource)
        );
        $renderer = new CachedDocumentRenderer($markdownRenderer);

        add_action('init', [$postType, 'register']);
        add_action('init', [$meta, 'register']);
        $editor->registerHooks();
        $transfer->registerHooks();
        $gettingStarted->registerHooks();
        $mermaidAssets->registerHooks();
        $mathAssets->registerHooks();

        add_filter(
            'the_content',
            [new DocumentContentFilter($renderer, $mermaidAssets, $mathAssets), 'filter']
        );

        add_shortcode(
            DocumentShortcode::TAG,
            [new DocumentShortcode($renderer, $mermaidAssets, $mathAssets), 'render']
        );

        register_activation_hook(
            $this->pluginFile,
            static function () use ($postType): void {
                $postType->register();
                flush_rewrite_rules();
            }
        );

        register_deactivation_hook(
            $this->pluginFile,
            static function (): void {
                flush_rewrite_rules();
            }
        );
    }

    public function loadTextDomain(): void
    {
        // Bundled Japanese translations provide a usable localized first release.
        // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
        load_plugin_textdomain(
            'ozeki-markdown-documents',
            plugin_rel_path: dirname(plugin_basename($this->pluginFile)) . '/languages'
        );
    }
}

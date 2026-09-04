<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments;

use OzekiMarkdownDocuments\Admin\DocumentEditor;
use OzekiMarkdownDocuments\Admin\DocumentTransfer;
use OzekiMarkdownDocuments\Content\DocumentMeta;
use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Content\MarkdownDocumentImporter;
use OzekiMarkdownDocuments\Content\MarkdownSource;
use OzekiMarkdownDocuments\Frontend\DocumentContentFilter;
use OzekiMarkdownDocuments\Frontend\DocumentShortcode;
use OzekiMarkdownDocuments\Rendering\CachedDocumentRenderer;
use OzekiMarkdownDocuments\Rendering\MarkdownRenderer;

final class Plugin
{
    public const VERSION = '0.1.0-dev';

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
        $postType = new DocumentPostType();
        $meta = new DocumentMeta();
        $markdownSource = new MarkdownSource();
        $editor = new DocumentEditor($meta, $markdownSource, $this->pluginFile);
        $transfer = new DocumentTransfer(
            new MarkdownDocumentImporter($markdownSource)
        );
        $renderer = new CachedDocumentRenderer(new MarkdownRenderer());

        add_action('init', [$postType, 'register']);
        add_action('init', [$meta, 'register']);
        $editor->registerHooks();
        $transfer->registerHooks();

        add_filter(
            'the_content',
            [new DocumentContentFilter($renderer), 'filter']
        );

        add_shortcode(
            DocumentShortcode::TAG,
            [new DocumentShortcode($renderer), 'render']
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
}

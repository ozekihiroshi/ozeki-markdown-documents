<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Admin;

use OzekiMarkdownDocuments\Content\DocumentPostType;
use OzekiMarkdownDocuments\Content\ExampleDocument;
use OzekiMarkdownDocuments\Content\MarkdownDocumentImporter;

final class GettingStartedPage
{
    private const CREATE_ACTION = 'ozmd_create_example_document';
    private const PAGE_SLUG = 'ozmd-getting-started';
    private const MERMAID_PAGE_SLUG = 'ozmd-mermaid-examples';
    private const MATH_PAGE_SLUG = 'ozmd-math-examples';

    /** @var list<string> */
    private array $hookSuffixes = [];

    public function __construct(
        private readonly DocumentEditor $editor,
        private readonly MarkdownDocumentImporter $importer
    ) {
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_post_' . self::CREATE_ACTION, [$this, 'createExampleDraft']);
    }

    public function registerPage(): void
    {
        $this->registerSubmenu(
            __('Markdown Guide', 'ozeki-markdown-documents'),
            __('Markdown Guide', 'ozeki-markdown-documents'),
            self::PAGE_SLUG,
        );
        $this->registerSubmenu(
            __('Mermaid Diagram Examples', 'ozeki-markdown-documents'),
            __('Mermaid Examples', 'ozeki-markdown-documents'),
            self::MERMAID_PAGE_SLUG,
        );
        $this->registerSubmenu(
            __('AsciiMath and LaTeX Examples', 'ozeki-markdown-documents'),
            __('Math Examples', 'ozeki-markdown-documents'),
            self::MATH_PAGE_SLUG,
        );
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if (! in_array($hookSuffix, $this->hookSuffixes, true)) {
            return;
        }

        $this->editor->enqueuePreviewAssets(0);
    }

    public function render(): void
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('You are not allowed to view the Markdown guide.', 'ozeki-markdown-documents'));
        }

        $documentsUrl = admin_url('edit.php?post_type=' . DocumentPostType::POST_TYPE);
        $newDocumentUrl = admin_url('post-new.php?post_type=' . DocumentPostType::POST_TYPE);
        $importUrl = admin_url(
            'edit.php?post_type=' . DocumentPostType::POST_TYPE . '&page=ozmd-import'
        );
        $guideKey = $this->selectedGuide();
        $example = ExampleDocument::guide($guideKey);

        echo '<div class="wrap ozmd-guide">';
        if ($guideKey === ExampleDocument::MARKDOWN) {
            echo '<h1>' . esc_html__('Ozeki Markdown Documents Guide', 'ozeki-markdown-documents') . '</h1>';
        } else {
            echo '<p><a href="' . esc_url($this->guideUrl(self::PAGE_SLUG)) . '">';
            echo esc_html__('← Back to the Markdown Guide', 'ozeki-markdown-documents');
            echo '</a></p>';
            echo '<h1>' . esc_html($example['title']) . '</h1>';
        }
        echo '<p class="description ozmd-guide-intro">';
        if ($guideKey === ExampleDocument::MERMAID) {
            echo esc_html__(
                'Explore common diagram types as readable Mermaid source beside the rendered result.',
                'ozeki-markdown-documents'
            );
        } elseif ($guideKey === ExampleDocument::MATH) {
            echo esc_html__(
                'Compare approachable AsciiMath with standard LaTeX and copy derived LaTeX from the preview.',
                'ozeki-markdown-documents'
            );
        } else {
            echo esc_html__(
                'Write portable Markdown, confirm the result beside it, and keep the original source available for import and export.',
                'ozeki-markdown-documents'
            );
        }
        echo '</p>';

        if ($guideKey === ExampleDocument::MARKDOWN) {
            echo '<div class="ozmd-guide-actions">';
            echo '<a class="button button-primary" href="' . esc_url($newDocumentUrl) . '">';
            echo esc_html__('Create a blank document', 'ozeki-markdown-documents');
            echo '</a>';
            echo '<a class="button" href="' . esc_url($importUrl) . '">';
            echo esc_html__('Import a .md file', 'ozeki-markdown-documents');
            echo '</a>';
            echo '<a class="button" href="' . esc_url($documentsUrl) . '">';
            echo esc_html__('View all documents', 'ozeki-markdown-documents');
            echo '</a>';
            echo '</div>';

            echo '<div class="ozmd-guide-steps">';
            $this->renderStep('1', __('Create or import', 'ozeki-markdown-documents'), __('Start with a blank document or import an existing UTF-8 Markdown file.', 'ozeki-markdown-documents'));
            $this->renderStep('2', __('Write and preview', 'ozeki-markdown-documents'), __('Keep Markdown on the left while checking the safe rendered result on the right.', 'ozeki-markdown-documents'));
            $this->renderStep('3', __('Publish and reuse', 'ozeki-markdown-documents'), __('Use the document permalink, reference it with a shortcode, or export the original .md file.', 'ozeki-markdown-documents'));
            echo '</div>';
        }

        $this->renderSpecializedGuides($guideKey);

        echo '<hr>';
        echo '<div class="ozmd-guide-section-heading">';
        echo '<div>';
        echo '<h2>' . esc_html($example['title']) . '</h2>';
        echo '<p>';
        echo esc_html__(
            'Edit the sample locally in this page and compare it with the live preview. Changes here are not saved.',
            'ozeki-markdown-documents'
        );
        echo '</p>';
        echo '</div>';
        echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">';
        echo '<input type="hidden" name="action" value="' . esc_attr(self::CREATE_ACTION) . '">';
        echo '<input type="hidden" name="guide" value="' . esc_attr($guideKey) . '">';
        wp_nonce_field(self::CREATE_ACTION);
        submit_button(
            __('Create this example as a new draft', 'ozeki-markdown-documents'),
            'secondary',
            'submit',
            false
        );
        echo '</form>';
        echo '</div>';

        echo '<div class="ozmd-editor-grid ozmd-guide-playground">';
        echo '<section class="ozmd-editor-pane">';
        echo '<label class="ozmd-pane-heading" for="ozmd-source">';
        echo esc_html__('Markdown — editable practice area', 'ozeki-markdown-documents');
        echo '</label>';
        echo '<textarea id="ozmd-source" class="widefat code ozmd-source" rows="36" spellcheck="false">';
        echo esc_textarea($example['source']);
        echo '</textarea>';
        echo '</section>';
        echo '<section class="ozmd-preview-pane">';
        echo '<div class="ozmd-preview-heading">';
        echo '<span>' . esc_html__('Preview', 'ozeki-markdown-documents') . '</span>';
        echo '<span id="ozmd-preview-status" class="ozmd-preview-status" role="status" aria-live="polite"></span>';
        echo '</div>';
        echo '<div id="ozmd-preview" class="ozmd-preview" aria-live="polite"></div>';
        echo '</section>';
        echo '</div>';
        echo '</div>';
    }

    public function createExampleDraft(): void
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('You are not allowed to create Markdown documents.', 'ozeki-markdown-documents'));
        }

        check_admin_referer(self::CREATE_ACTION);

        $guideKey = isset($_POST['guide'])
            ? sanitize_key(wp_unslash($_POST['guide']))
            : ExampleDocument::MARKDOWN;
        $example = ExampleDocument::guide($guideKey);

        $postId = $this->importer->createDraft(
            $example['filename'],
            $example['source']
        );

        if (is_wp_error($postId)) {
            wp_die(esc_html($postId->get_error_message()));
        }

        $editUrl = get_edit_post_link($postId, 'raw');
        if (! is_string($editUrl) || $editUrl === '') {
            wp_die(esc_html__('The example draft was created but its editing URL is unavailable.', 'ozeki-markdown-documents'));
        }

        wp_safe_redirect($editUrl);
        exit;
    }

    private function renderStep(string $number, string $title, string $description): void
    {
        echo '<section class="ozmd-guide-step">';
        echo '<span class="ozmd-guide-step-number" aria-hidden="true">' . esc_html($number) . '</span>';
        echo '<div><h2>' . esc_html($title) . '</h2><p>' . esc_html($description) . '</p></div>';
        echo '</section>';
    }

    private function registerSubmenu(string $pageTitle, string $menuTitle, string $slug): void
    {
        $hookSuffix = add_submenu_page(
            'edit.php?post_type=' . DocumentPostType::POST_TYPE,
            $pageTitle,
            $menuTitle,
            'edit_posts',
            $slug,
            [$this, 'render']
        );

        if (is_string($hookSuffix)) {
            $this->hookSuffixes[] = $hookSuffix;
        }
    }

    private function selectedGuide(): string
    {
        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

        if ($page === self::MERMAID_PAGE_SLUG) {
            return ExampleDocument::MERMAID;
        }

        if ($page === self::MATH_PAGE_SLUG) {
            return ExampleDocument::MATH;
        }

        return ExampleDocument::MARKDOWN;
    }

    private function renderSpecializedGuides(string $selected): void
    {
        echo '<nav class="ozmd-guide-library" aria-label="';
        echo esc_attr__('Example library', 'ozeki-markdown-documents');
        echo '">';
        $this->renderGuideLink(
            self::PAGE_SLUG,
            ExampleDocument::MARKDOWN,
            __('Markdown essentials', 'ozeki-markdown-documents'),
            __('A disciplined path from paragraphs to publishing and reuse.', 'ozeki-markdown-documents'),
            $selected
        );
        $this->renderGuideLink(
            self::MERMAID_PAGE_SLUG,
            ExampleDocument::MERMAID,
            __('Mermaid diagram library', 'ozeki-markdown-documents'),
            __('Flow, sequence, state, class, ER, Gantt and pie diagrams.', 'ozeki-markdown-documents'),
            $selected
        );
        $this->renderGuideLink(
            self::MATH_PAGE_SLUG,
            ExampleDocument::MATH,
            __('AsciiMath and LaTeX library', 'ozeki-markdown-documents'),
            __('Fractions through calculus, matrices, sets and multi-line formulas.', 'ozeki-markdown-documents'),
            $selected
        );
        echo '</nav>';
    }

    private function renderGuideLink(
        string $pageSlug,
        string $guideKey,
        string $title,
        string $description,
        string $selected
    ): void {
        $current = $guideKey === $selected;
        echo '<a class="ozmd-guide-library-item';
        echo $current ? ' is-current' : '';
        echo '" href="' . esc_url($this->guideUrl($pageSlug)) . '"';
        echo $current ? ' aria-current="page"' : '';
        echo '><strong>' . esc_html($title) . '</strong>';
        echo '<span>' . esc_html($description) . '</span></a>';
    }

    private function guideUrl(string $pageSlug): string
    {
        return admin_url(
            'edit.php?post_type=' . DocumentPostType::POST_TYPE
            . '&page=' . $pageSlug
        );
    }
}

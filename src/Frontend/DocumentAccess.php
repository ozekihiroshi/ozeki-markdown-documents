<?php

declare(strict_types=1);

namespace OzekiMarkdownDocuments\Frontend;

use OzekiMarkdownDocuments\Content\DocumentPostType;

/** Checks the referenced document before any rendered HTML (including cache) is read. */
final class DocumentAccess
{
    public static function denial(\WP_Post $document): ?string
    {
        if ($document->post_type !== DocumentPostType::POST_TYPE
            || in_array($document->post_status, ['trash', 'auto-draft'], true)
            || ($document->post_status !== 'publish' && !current_user_can('read_post', $document->ID))) {
            return 'restricted';
        }
        if (post_password_required($document)) {
            return 'password';
        }

        $setting = (string) get_post_meta($document->ID, '_wpmem_block', true);
        $settings = get_option('wpmembers_settings', []);
        $recordedRestriction = in_array($setting, ['1', '2'], true)
            || ($setting === '' && (bool) get_post_meta($document->ID, 'block', true))
            || ($setting === '' && empty(get_post_meta($document->ID, 'unblock', true))
                && is_array($settings) && (int) ($settings['block'][$document->post_type] ?? 0) === 1);

        if (!function_exists('wpmem_is_blocked')) {
            return $recordedRestriction ? 'membership_unavailable' : null;
        }

        // WP-Members and its filters may consult the global post as well as the ID.
        global $post, $wpmem;
        $previous = $post;
        $post = $document;
        try {
            $blocked = wpmem_is_blocked($document->ID) || $setting === '2';
            $memberships = function_exists('wpmem_get_post_memberships')
                ? wpmem_get_post_memberships($document->ID) : [];
            // Paid/expiration extensions are outside this basic integration contract.
            if ($memberships || ($blocked && !empty($wpmem->use_exp))) {
                return 'membership_unsupported';
            }
            if ($blocked && !is_user_logged_in()) {
                return 'restricted';
            }
            if (!is_object($wpmem) || !is_callable([$wpmem, 'do_securify'])) {
                return 'membership_unavailable';
            }
            // Provider filters can also restrict an otherwise public document.
            // Evaluate them without supplying the document body or a cached copy.
            $probe = 'ozmd-access-' . wp_generate_uuid4();
            if ($wpmem->do_securify($probe) !== $probe) {
                return 'restricted';
            }
        } finally {
            $post = $previous;
        }
        return null;
    }

    public static function message(string $reason): string
    {
        if (current_user_can('manage_options') && in_array($reason, ['membership_unavailable', 'membership_unsupported'], true)) {
            return '<p>' . esc_html__('This document has membership restrictions that cannot currently be checked. Check the membership plugin and its settings.', 'ozeki-markdown-documents') . '</p>';
        }
        return '<p>' . esc_html__('This document is not available to you.', 'ozeki-markdown-documents') . '</p>';
    }
}

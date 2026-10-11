<?php
/**
 * Plugin Name: Ozeki Markdown Documents
 * Plugin URI: https://github.com/ozekihiroshi/ozeki-markdown-documents
 * Description: Manage Markdown as independent, reusable WordPress documents.
 * Version: 0.1.2
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Hiroshi Ozeki
 * Author URI: https://github.com/ozekihiroshi
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ozeki-markdown-documents
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$ozmd_autoload_path = __DIR__ . '/vendor/autoload.php';

if (! is_readable($ozmd_autoload_path)) {
    add_action(
        'admin_notices',
        static function (): void {
            $screen = get_current_screen();
            if (! current_user_can('activate_plugins')
                || ! $screen instanceof WP_Screen || $screen->id !== 'plugins') {
                return;
            }

            echo '<div class="notice notice-error"><p>';
            echo esc_html__(
                'Ozeki Markdown Documents is incomplete because its Composer dependencies are missing. Reinstall the complete plugin ZIP to resolve this error.',
                'ozeki-markdown-documents'
            );
            echo '</p></div>';
        }
    );

    return;
}

require $ozmd_autoload_path;

OzekiMarkdownDocuments\Plugin::boot(__FILE__);

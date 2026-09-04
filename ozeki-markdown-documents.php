<?php
/**
 * Plugin Name: Ozeki Markdown Documents
 * Description: Manage Markdown as independent, reusable WordPress documents.
 * Version: 0.1.0-dev
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: Hiroshi Ozeki
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
            if (! current_user_can('activate_plugins')) {
                return;
            }

            echo '<div class="notice notice-error"><p>';
            echo esc_html__(
                'Ozeki Markdown Documents is incomplete because its Composer dependencies are missing.',
                'ozeki-markdown-documents'
            );
            echo '</p></div>';
        }
    );

    return;
}

require $ozmd_autoload_path;

OzekiMarkdownDocuments\Plugin::boot(__FILE__);

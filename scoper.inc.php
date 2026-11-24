<?php

declare(strict_types=1);

return [
    'prefix' => 'OzekiMarkdownDocumentsVendor',
    'php-version' => '8.1',
    'exclude-namespaces' => [
        'OzekiMarkdownDocuments',
    ],
    'exclude-classes' => [
        'WP_Error',
        'WP_Post',
        'WP_Screen',
    ],
    // These functions belong to the separately installed membership provider.
    'exclude-functions' => [
        'wpmem_is_blocked',
        'wpmem_get_post_memberships',
    ],
    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,
];

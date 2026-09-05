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
    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,
];

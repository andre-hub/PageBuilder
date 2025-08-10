<?php

declare(strict_types=1);

/* For security */
ini_set('allow_url_fopen', 0);
ini_set('display_errors', 'on');
define('BUILDER', true);

/* Debug Timer */
$RUNTIMER = microtime(true);

/* Load Composer autoloader - this handles all our modern PHP 8 classes */
require_once __DIR__ . '/../vendor/autoload.php';

/* Load SiteConfig from root */
require_once __DIR__ . '/../SiteConfig.php';

// Legacy function includes for backward compatibility
// These will be refactored to classes in future iterations
$legacyFiles = [
    'Builder/StaticSiteBuilder.php',
    'Builder/FileHelper.php', 
    'Builder/Debug.php',
    'Parser/MarkdownParser.php'
];

foreach ($legacyFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        require_once __DIR__ . '/' . $file;
    }
}

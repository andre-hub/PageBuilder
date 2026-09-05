<?php

declare(strict_types=1);

/* For security */
ini_set('allow_url_fopen', 0);
define('BUILDER', true);

/* Debug Timer */
$RUNTIMER = microtime(true);

/*
 * Verzeichnis dieses Builders und, davon abgeleitet, das Projektverzeichnis.
 * Wird u. a. gebraucht, um ein falsch konfiguriertes Ausgabeziel zu
 * erkennen, das das Projekt selbst treffen wuerde (siehe Review-Befund 3).
 */
define('PAGEBUILDER_DIR', dirname(__DIR__));
define('PAGEBUILDER_PROJECT_ROOT', PAGEBUILDER_SITE_ROOT);

/* Load Composer autoloader - this handles all our modern PHP 8 classes */
$autoload = PAGEBUILDER_DIR . '/vendor/autoload.php';
if (!is_readable($autoload)) {
    throw new RuntimeException();
}
require_once $autoload;

/* Load SiteConfig from root */
if (!chdir(PAGEBUILDER_SITE_DIR)) {
    throw new RuntimeException();
}
require_once PAGEBUILDER_SITE_DIR . '/SiteConfig.php';

// Legacy function includes for backward compatibility
// These will be refactored to classes in future iterations
$legacyFiles = [
    'Builder/StaticSiteBuilder.php',
    'Builder/Sitemap.php',
    'Builder/FileHelper.php', 
    'Builder/Debug.php',
    'Parser/MarkdownParser.php'
];

foreach ($legacyFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        require_once __DIR__ . '/' . $file;
    }
}

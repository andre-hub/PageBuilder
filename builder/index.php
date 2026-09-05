<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '0');
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

// Do not expose configuration values, file paths or stack traces on failure.
set_error_handler(static function (int $severity): bool {
    if (error_reporting() & $severity) {
        throw new RuntimeException('Build failed.');
    }
    return true;
});

try {
    define('DEBUG', false);
    define('PAGEBUILDER_CORE_ROOT', dirname(__DIR__));
    $arguments = array_slice($argv, 1);
    if ($arguments === []) {
        $site = PAGEBUILDER_CORE_ROOT;
    } elseif (count($arguments) === 2 && $arguments[0] === '--site' && trim($arguments[1]) !== '') {
        // Resolve before SiteConfig changes the working directory.
        $site = realpath($arguments[1]);
    } else {
        throw new RuntimeException();
    }
    if ($site === false || !is_dir($site) || !is_dir($site . '/builder')
        || is_link($site . '/builder') || is_link($site . '/builder/SiteConfig.php')
        || !is_readable($site . '/builder/SiteConfig.php')) {
        throw new RuntimeException();
    }
    define('PAGEBUILDER_SITE_ROOT', $site);
    define('PAGEBUILDER_SITE_DIR', $site . '/builder');
    require_once __DIR__ . '/src/include.php';
    exit(BuilderMain() ? 0 : 1);
} catch (Throwable $exception) {
    fwrite(STDERR, "Fehler: Website konnte nicht gebaut werden. Konfiguration und Abhaengigkeiten pruefen.\n");
    exit(1);
}

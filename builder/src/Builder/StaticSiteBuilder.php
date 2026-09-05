<?php

declare(strict_types=1);

/**
 * Baut die gesamte Website. Gibt true bei vollstaendigem Erfolg zurueck,
 * sonst false - ein fehlgeschlagener Build muss sich im Rueckgabewert (und
 * damit im Exitcode von index.php) niederschlagen, statt still als Erfolg
 * durchzulaufen (siehe Review-Befund 12).
 */
function BuilderMain(): bool {
	try {
		$pageTree = InitBuilder();
	} catch (\RuntimeException $exception) {
		fwrite(STDERR, "Fehler: Ungueltige Website-Konfiguration.\n");
		return false;
	}
	return BuildingFiles($pageTree) && BuildSitemap($pageTree);
}

/**
 * @throws RuntimeException wenn Ausgabeziel, Quellverzeichnis oder Template
 *                          nicht sicher bzw. nicht lesbar sind
 */
function InitBuilder() {
	SetDebug();
	$pageTree = PageTree();
	if (!BuildPageDir($pageTree['BuildPagePath'], $pageTree['SourcePath'])) {
		throw new RuntimeException(
			"Ausgabeverzeichnis '{$pageTree['BuildPagePath']}' konnte nicht neu angelegt werden."
		);
	}
	MarkdownParserLoad($pageTree['MarkdownParser'], $pageTree['VendorLib']);
	return $pageTree;
}

function PageTree() {
	$pageTree = SiteConfig::load();
	// Config paths belong to the selected site, never to the caller or core.
	chdir(PAGEBUILDER_SITE_DIR);
	foreach (['SourcePath', 'StaticPath'] as $key) {
		$pageTree[$key] = ResolveSiteDirectory($pageTree[$key]) . '/';
	}
	$pageTree['BuildPagePath'] = ResolveBuildTarget($pageTree['BuildPagePath'], $pageTree['SourcePath']) . '/';

	if (!is_dir($pageTree['SourcePath'])) {
		throw new RuntimeException("Quellverzeichnis '{$pageTree['SourcePath']}' existiert nicht.");
	}

	$theme     = $pageTree['Theme']  ?? 'default';
	$themePath = $pageTree['ThemePath'] ?? '../static/themes/';
	$themePath = ResolveSiteDirectory($themePath, false) . '/';
	if (!is_string($theme) || !preg_match('/\A[A-Za-z0-9_-]+\z/D', $theme)) {
		throw new RuntimeException();
	}
	$pageTree['Theme']     = $theme;
	$pageTree['ThemePath'] = $themePath;
	$pageTree['Lang']      = $pageTree['Lang'] ?? 'de';
	$target = rtrim($pageTree['BuildPagePath'], '/');
	foreach ([$pageTree['StaticPath'], $themePath] as $protected) {
		$protected = rtrim($protected, '/');
		if (IsPathWithin($target, $protected) || IsPathWithin($protected, $target)) {
			throw new RuntimeException();
		}
	}
	$sitemap = PAGEBUILDER_SITE_ROOT . '/sitemap.xml';
	if (is_link($sitemap) || (file_exists($sitemap) && !is_file($sitemap))) {
		throw new RuntimeException();
	}
	// Validate source links before replacing existing HTML.
	getPathEntry($pageTree['SourcePath']);

	checkThemeApiVersion($theme, $themePath);

	$pageHtml = LoadTplFile($pageTree['StaticPath'] . '/html/', 'index', 'html');
	if ($pageHtml === false) {
		throw new RuntimeException(
			"Seitentemplate unter '{$pageTree['StaticPath']}/html/index.html' ist nicht lesbar."
		);
	}
	$pageTree['PageHtml']  = $pageHtml;
	$pageTree['ParseTags'] = ['NAV', 'SITETITLE', 'TEXT', 'SITENAME', 'SITEURL', 'LANG', 'SKIPLINK', 'THEME_NAME'];
	$pageTree['ContentRenderer'] = \PageBuilder\Markdown\ContentRenderer::create($pageTree);
	return $pageTree;
}

function checkThemeApiVersion(string $theme, string $themePath): void {
	$tokensFile = $themePath . $theme . '/tokens.css';
	if (!IsPathWithin(CanonicalPath($tokensFile), PAGEBUILDER_SITE_ROOT)) {
		throw new RuntimeException();
	}
	if (!file_exists($tokensFile)) {
		return;
	}
	$head = file_get_contents($tokensFile, false, null, 0, 200);
	if ($head === false) {
		return;
	}
	if (!preg_match('/theme-api:\s*(\d+)\.\d+/', $head, $m)) {
		return;
	}
	$themeMajor = (int) $m[1];
	$coreMajor  = (int) explode('.', THEME_API_VERSION)[0];
	if ($themeMajor !== $coreMajor) {
		fwrite(STDERR, "Warnung: Theme-API ist nicht kompatibel. Layout kann abweichen.\n");
	}
}

function MarkdownParserLoad($markdownParser, $vendorlib): void {
	switch ($markdownParser) {
		case 'CommonMark':
		default:
			// League/CommonMark wird über den Composer-Autoloader geladen.
			break;
	}
}

/**
 * Baut alle Seiten. Gibt true zurueck, wenn jede Seite erfolgreich
 * geschrieben wurde, sonst false (siehe Review-Befund 12) - der Build laeuft
 * dabei bewusst weiter, damit ein einzelner Fehler nicht alle anderen,
 * funktionierenden Seiten verhindert; der Gesamterfolg bleibt aber korrekt
 * im Rueckgabewert sichtbar.
 */
function BuildingFiles($pageTree): bool {
	$pages   = getPathEntry($pageTree['SourcePath']);
	$success = true;
	while ($pages) {
		$pagePart = array_pop($pages);
		if (!parseFiles($pageTree, $pagePart)) {
			$success = false;
		}
	}
	return $success;
}

function SetDebug(): void {
	if (DEBUG === true) {
		ini_set('error_reporting', (string) E_ALL);
		ini_set('xdebug.collect_params', '4');
	} else {
		ini_set('error_reporting', (string) E_ALL);
	}
}

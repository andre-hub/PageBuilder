<?php

declare(strict_types=1);

/**
 * Baut die Sitemap aus denselben Quellen wie die HTML-Seiten, unabhaengig
 * von NavHidden. Erst eine vollstaendig geschriebene Datei ersetzt das Ziel.
 */
function BuildSitemap(array $pageTree): bool {
	$temporary = null;
	try {
		$source = rtrim((string) $pageTree['SourcePath'], '/') . '/';
		$pages = @getPathEntry($pageTree['SourcePath']);
		if ($pages === []) {
			throw new RuntimeException();
		}

		$siteUrl = 'https://' . rtrim((string) $pageTree['SiteUrl'], '/') . '/';
		$entries = [];
		foreach ($pages as $slug) {
			$modified = @filemtime($source . $slug . MarkdownExtension());
			if ($modified === false) {
				throw new RuntimeException();
			}
			$isStart = in_array($slug, ['index', 'home', 'startseite'], true);
			$entries[] = [
				'loc' => $isStart ? $siteUrl : $siteUrl . rawurlencode($slug),
				'lastmod' => date('Y-m-d', $modified),
				'start' => $isStart,
				'slug' => $slug,
			];
		}
		usort($entries, static function (array $a, array $b): int {
			return ($b['start'] <=> $a['start'])
				?: strnatcasecmp($a['slug'], $b['slug'])
				?: strcmp($a['slug'], $b['slug']);
		});

		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		foreach ($entries as $entry) {
			$xml .= "<url>\n";
			$xml .= "\t<loc>" . htmlspecialchars($entry['loc'], ENT_QUOTES | ENT_XML1, 'UTF-8') . "</loc>\n";
			$xml .= "\t<lastmod>" . $entry['lastmod'] . "</lastmod>\n";
			$xml .= "\t<changefreq>monthly</changefreq>\n";
			if ($entry['start']) {
				$xml .= "\t<priority>1.0</priority>\n";
			}
			$xml .= "</url>\n";
		}
		$xml .= "</urlset>\n";

		$directory = PAGEBUILDER_SITE_ROOT;
		$target = $directory . '/sitemap.xml';
		if (!is_dir($directory) || !is_writable($directory)
			|| is_link($target) || (file_exists($target) && !is_file($target))) {
			throw new RuntimeException();
		}
		$temporary = @tempnam($directory, '.sitemap-');
		// tempnam kann bei unbeschreibbarem Ziel in das System-Temp ausweichen.
		if ($temporary === false || realpath(dirname($temporary)) !== realpath($directory)) {
			throw new RuntimeException();
		}
		if (@file_put_contents($temporary, $xml, LOCK_EX) !== strlen($xml)
			|| !@chmod($temporary, 0644 & ~umask())) {
			throw new RuntimeException();
		}
		clearstatcache(true, $target);
		if (is_link($target) || !@rename($temporary, $target)) {
			throw new RuntimeException();
		}
		$temporary = null;
		return true;
	} catch (\Throwable $exception) {
		fwrite(STDERR, "Fehler: Sitemap konnte nicht erstellt werden.\n");
		return false;
	} finally {
		if (is_string($temporary)) {
			@unlink($temporary);
		}
	}
}

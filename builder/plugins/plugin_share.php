<?php

declare(strict_types=1);

/**
 * Beispielplugin: ersetzt Platzhalter der Form {SCHLUESSEL} durch Werte aus
 * SiteConfig::getPluginSettings(). Wird nur geladen, wenn "share" in
 * SiteConfig::loadPlugins() aufgefuehrt ist (siehe Review-Befund 20).
 */
function PluginShare($pluginSettings, $pageHtml) {
	return PluginShareParser($pluginSettings, $pageHtml);
}

function PluginShareParser($pluginSettings, $pageHtml) {
	foreach ($pluginSettings as $setting) {
		if (!is_array($setting)) {
			continue;
		}
		foreach ($setting as $key => $value) {
			$pageHtml = MergeHtml($pageHtml, strtoupper((string) $key), (string) $value, false);
		}
	}
	return $pageHtml;
}

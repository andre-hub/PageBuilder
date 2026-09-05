<?php

declare(strict_types=1);

function CaseParser($pageTree, $txtMarkdown) {
	$parseTags = $pageTree['ParseTags'];
	while ($parseTags) {
		$part = array_pop($parseTags);
		if ($part === null) {
			continue;
		}
		switch ($part) {
			case 'NAV':
				$pageTree['PageHtml'] = str_replace('{' . $part . '}', MakeHTMLMenu($pageTree), $pageTree['PageHtml']);
				break;

			case 'TEXT':
				$html = $pageTree['ContentRenderer']->render($txtMarkdown);
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'], $part, $html, false);
				break;

			case 'SITEURL':
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'], $part, MakeUrl($pageTree['SiteUrl'], true), false);
				break;

			case 'SITENAME':
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'], $part, $pageTree['SiteName'], false);
				break;

			case 'SITETITLE':
				// Der Titel stammt aus Dateiname, Quelltext oder Konfiguration
				// und landet in <title> wie in <h1>; beides ist Textkontext.
				$titel = htmlspecialchars($pageTree['SiteTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'], $part, $titel, false);
				break;

			case 'LANG':
				$lang = htmlspecialchars($pageTree['Lang'] ?? 'de', ENT_QUOTES, 'UTF-8');
				$pageTree['PageHtml'] = str_replace('{LANG}', $lang, $pageTree['PageHtml']);
				break;

			case 'SKIPLINK':
				$skipLink = '<a href="#main-content" class="skip-link">Zum Inhalt springen</a>';
				$pageTree['PageHtml'] = str_replace('{SKIPLINK}', $skipLink, $pageTree['PageHtml']);
				break;

			case 'THEME_NAME':
				$themeName = htmlspecialchars($pageTree['Theme'] ?? 'default', ENT_QUOTES, 'UTF-8');
				$pageTree['PageHtml'] = str_replace('{THEME_NAME}', $themeName, $pageTree['PageHtml']);
				break;

			default:
				$pageTree['PageHtml'] = MergeHtml($pageTree['PageHtml'], $part, '');
				break;
		}
	}
	return $pageTree;
}

function ParseFilter() {
	return ['agb', 'impressum', 'imprint'];
}

function StartpageFilter() {
	return ['index', 'home', 'startseite'];
}

function BeginWith($checkStr, $searchStr) {
	if (substr($checkStr, 0, strlen($searchStr)) === $searchStr) {
		return true;
	}
	return false;
}

function EndWith($checkStr, $searchStr, $endPos = 1) {
	if (substr($checkStr, strlen($checkStr) - $endPos, $endPos) === $searchStr) {
		return true;
	}
	return false;
}

function MakeUrl($siteUrl = '', $isHttps = false) {
	if (!EndWith($siteUrl, '/') && !EndWith($siteUrl, 'html', 4)) {
		$siteUrl .= '/';
	}
	$siteUrl = AddHttp($siteUrl, $isHttps);
	return $siteUrl;
}

function AddHttp($siteUrl, $isHttps) {
	$http  = 'http://';
	$https = 'https://';
	if (!BeginWith($siteUrl, $http) && !BeginWith($siteUrl, $https)) {
		$siteUrl = $isHttps === true ? $https . $siteUrl : $http . $siteUrl;
	}
	return $siteUrl;
}

function MakeHTMLMenu($pageTree) {
	$nav     = '';
	$pages   = getPathEntry($pageTree['SourcePath']);
	$current = $pageTree['CurrentPage'] ?? '';
	$labels  = $pageTree['NavLabels'] ?? [];
	$order   = $pageTree['NavOrder'] ?? [];
	$hidden  = $pageTree['NavHidden'] ?? [];
	foreach (SortNavPages($pages, $order) as $pagePart) {
		// In NavOrder ausdruecklich aufgefuehrte Seiten gehoeren ins Menue,
		// auch wenn ein Muster in NavHidden auf sie passt.
		if (!in_array($pagePart, $order, true) && IsHiddenFromNav($pagePart, $hidden)) {
			continue;
		}
		$nav .= MakeNav($pagePart, $pageTree['SiteUrl'], $current, $labels);
	}
	return $nav;
}

/**
 * Seiten, die es geben soll, aber nicht im Menue: exakter Name oder Muster
 * mit * als Platzhalter, etwa 'ki-*' fuer alle Unterseiten eines Themas.
 * Die Seiten bleiben erreichbar und stehen weiter in der Sitemap.
 */
function IsHiddenFromNav(string $pagePart, array $hidden): bool {
	foreach ($hidden as $pattern) {
		if ($pattern === $pagePart) {
			return true;
		}
		if (strpos($pattern, '*') !== false && fnmatch($pattern, $pagePart)) {
			return true;
		}
	}
	return false;
}

/**
 * Bringt die Menueintraege in die in SiteConfig festgelegte Reihenfolge.
 * Nicht aufgefuehrte Seiten haengen alphabetisch hinten an.
 */
function SortNavPages(array $pages, array $order): array {
	$ranked = [];
	foreach ($pages as $page) {
		$pos      = array_search($page, $order, true);
		$ranked[] = [$pos === false ? PHP_INT_MAX : $pos, $page];
	}
	usort($ranked, static function (array $a, array $b): int {
		return $a[0] <=> $b[0] ?: strnatcasecmp($a[1], $b[1]);
	});
	return array_column($ranked, 1);
}

/**
 * Menuebeschriftung: bevorzugt der in SiteConfig gepflegte Text, sonst der
 * Dateiname mit Bindestrichen als Leerzeichen und grossem Anfangsbuchstaben.
 */
function NavLabel(string $pagePart, array $labels): string {
	if (isset($labels[$pagePart])) {
		return $labels[$pagePart];
	}
	$label = str_replace(['-', '_'], ' ', $pagePart);
	return mb_strtoupper(mb_substr($label, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($label, 1, null, 'UTF-8');
}

function MakeNav($pagePart, $siteUrl, $currentPage = '', array $labels = []) {
	$filter          = ParseFilter();
	$startpageFilter = StartpageFilter();
	if (in_array($pagePart, $filter, true)) {
		return '';
	}
	if (!in_array($pagePart, $startpageFilter, true)) {
		// Der Dateiname ist ein einzelnes Adresssegment und wird als solches
		// kodiert, bevor er als Attributwert ausgegeben wird.
		$url = MakeUrl($siteUrl, true) . rawurlencode($pagePart);
	} else {
		$url = MakeUrl($siteUrl, true);
	}
	$url = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	$label   = htmlspecialchars(NavLabel($pagePart, $labels), ENT_QUOTES, 'UTF-8');
	$current = ($pagePart === $currentPage) ? ' aria-current="page"' : '';
	return '<li><a href="' . $url . '"' . $current . '>' . $label . '</a></li>';
}


function MergeHtml($html, $part, $txt = '', $filter = false) {
	if (strlen($html) === 0 || strlen($txt) === 0) {
		return str_replace('{' . $part . '}', '', $html);
	}
	$tmpTxt = $txt;
	return str_replace('{' . $part . '}', $tmpTxt, $html);
}

function GetTxt($Source, $id = 0) {
	$tmpTxt = '';
	if (!(@is_array($Source))) {
		return $tmpTxt;
	}
	$tmpTxt = trim($Source[$id > 0 ? $id : 0]);
	return $tmpTxt;
}


/**
 * Ermittelt den Seitentitel und liefert den ggf. gekuerzten Quelltext zurueck.
 *
 * 1. Ein Eintrag in PageTitles gewinnt immer.
 * 2. Beginnt die Quelldatei mit einer echten ATX-Ueberschrift (# bis ######,
 *    mit Leerraum oder Zeilenende danach - wie in der CommonMark-Spezifikation
 *    verlangt), wird deren Text zum Titel und die Zeile aus dem Text entfernt
 *    - sonst stuenden auf der Seite zwei konkurrierende Ueberschriften
 *    untereinander.
 * 3. Sonst der Dateiname mit Bindestrichen als Leerzeichen.
 *
 * @return array{title: string, markdown: string}
 */
function PageTitle(string $pagePart, string $txtMarkdown, array $titles): array {
	if (isset($titles[$pagePart])) {
		return ['title' => $titles[$pagePart], 'markdown' => $txtMarkdown];
	}

	$lines = preg_split('/\r\n|\r|\n/', $txtMarkdown) ?: [];
	foreach ($lines as $index => $line) {
		if (trim($line) === '') {
			continue;
		}
		$heading = ParseAtxHeading($line);
		if ($heading !== null) {
			unset($lines[$index]);
			return [
				'title'    => $heading,
				'markdown' => ltrim(implode("\n", $lines), "\n"),
			];
		}
		break; // erste sichtbare Zeile ist keine Ueberschrift
	}

	return ['title' => NavLabel($pagePart, []), 'markdown' => $txtMarkdown];
}

/**
 * Erkennt ein ATX-Heading nach CommonMark und liefert dessen Textinhalt,
 * oder null, wenn die Zeile keine gueltige Ueberschrift ist.
 *
 * Regeln: bis zu drei fuehrende Leerzeichen, danach 1-6 '#'; direkt danach
 * muss die Zeile enden oder ein Leerzeichen/Tab folgen ("#notheading" ist
 * damit keine Ueberschrift, unabhaengig vom Level). Ein optionaler
 * schliessender '#'-Lauf faellt weg, wenn ihm Leerraum vorausgeht oder er
 * den gesamten Rest bildet.
 *
 * Bewusst ohne regulaeren Ausdruck mit ueberlappenden Quantifizierern:
 * jeder Schritt ist eine lineare String-Operation, damit lange Zeilen kein
 * PCRE-Backtracking ausloesen koennen (siehe Review-Befunde 17 und 18).
 */
function ParseAtxHeading(string $line): ?string {
	$withoutLeading = ltrim($line, ' ');
	if (strlen($line) - strlen($withoutLeading) > 3) {
		return null;
	}

	$hashCount = 0;
	$len       = strlen($withoutLeading);
	while ($hashCount < $len && $withoutLeading[$hashCount] === '#') {
		$hashCount++;
	}
	if ($hashCount === 0 || $hashCount > 6) {
		return null;
	}

	$rest = substr($withoutLeading, $hashCount);
	if ($rest !== '' && $rest[0] !== ' ' && $rest[0] !== "\t") {
		return null; // z. B. "#notheading"
	}

	$content = trim($rest, " \t");

	// Optionalen schliessenden '#'-Lauf entfernen: nur wenn ihm Leerraum
	// vorausgeht oder er den kompletten Rest bildet.
	$withoutTrailingHashes = rtrim($content, '#');
	if ($withoutTrailingHashes !== $content) {
		if ($withoutTrailingHashes === ''
			|| substr($withoutTrailingHashes, -1) === ' '
			|| substr($withoutTrailingHashes, -1) === "\t"
		) {
			$content = rtrim($withoutTrailingHashes, " \t");
		}
	}

	return $content;
}

function ParseFiles($pageTree, $pagePart = ''): bool {
	if (IsDirSpecials($pagePart)) {
		return true;
	}
	$parsedFileName = $pagePart . '.html';
	if (strlen(trim($parsedFileName)) < 5) {
		return true;
	}

	$pageTree['CurrentPage'] = $pagePart;

	$txtMarkdown = loadTplFile($pageTree['SourcePath'], $pagePart, 'markdown');
	if ($txtMarkdown === false) {
		fwrite(STDERR, "Fehler: Quelldatei ist nicht lesbar.\n");
		return false;
	}

	// Seitentitel: erst die Konfiguration, dann die erste Ueberschrift der
	// Quelldatei, zuletzt der aufbereitete Dateiname.
	$title = PageTitle($pagePart, $txtMarkdown, $pageTree['PageTitles'] ?? []);
	$pageTree['SiteTitle'] = $title['title'];
	$txtMarkdown           = $title['markdown'];
	$pageTree = caseParser($pageTree, $txtMarkdown);

	$pluginPath    = PluginPath();
	$activePlugins = GetAllowPlugins($pluginPath);
	$pageTree      = PluginsLoader($pluginPath, $activePlugins, $pageTree);

	$written = fileWrite($pageTree['BuildPagePath'], $parsedFileName, $pageTree['PageHtml']);
	if ($written === false) {
		fwrite(STDERR, "Fehler: Seite konnte nicht geschrieben werden.\n");
		return false;
	}

	return true;
}

/**
 * Ort der mitgelieferten Plugin-Dateien, relativ zum Arbeitsverzeichnis des
 * Builders (das ist nach SiteConfig::load() immer builder/). Muss mit dem
 * Ablageort in diesem Repository uebereinstimmen (siehe Review-Befund 20).
 */
function PluginPath(): string {
	return 'plugins';
}

/**
 * Ermittelt, welche im Pluginverzeichnis gefundenen Plugins tatsaechlich
 * aktiv sind: nur Dateien mit dem Praefix "plugin", deren abgeleiteter Name
 * in der von SiteConfig::loadPlugins() gepflegten Freigabeliste steht. Ohne
 * Eintrag in der Freigabeliste wird ein gefundenes Plugin nicht geladen
 * (siehe Review-Befund 20).
 *
 * @return list<string>
 */
function GetAllowPlugins($path) {
	$filePrefix      = 'plugin';
	$pluginFilenames = getPathEntry($path) ?: [];
	$allowedPlugins  = SiteConfig::loadPlugins();
	$activePlugins   = [];
	foreach ($pluginFilenames as $pluginFilename) {
		if (!BeginWith($pluginFilename, $filePrefix)) {
			continue;
		}
		$pluginName = str_replace('_', '', str_replace($filePrefix, '', $pluginFilename));
		if (in_array($pluginName, $allowedPlugins, true)) {
			$activePlugins[] = $pluginName;
		}
	}
	return $activePlugins;
}

function PluginsLoader($path, $activePlugins, $pageTree) {
	if (!$activePlugins) {
		return $pageTree;
	}
	while ($activePlugins) {
		$pluginName = array_pop($activePlugins);
		if ($pluginName === null) {
			return $pageTree;
		}
		$pageTree['PageHtml'] = PluginStart($path, $pluginName, $pageTree['PageHtml']);
	}
	return $pageTree;
}

/**
 * Laedt genau die Datei, in der GetAllowPlugins() das Plugin gefunden hat,
 * und ruft dessen Einstiegsfunktion auf. Kein "using()"-Aufruf mehr: die
 * Funktion existiert im Laufzeitcode nicht (siehe Review-Befund 20).
 */
function PluginStart($path, $pluginName, $pageHtml) {
	$prefix = 'plugin';
	$file   = rtrim($path, '/') . '/' . $prefix . '_' . $pluginName . '.php';
	if (!is_file($file)) {
		fwrite(STDERR, "Warnung: Plugin-Datei nicht gefunden, wird uebersprungen.\n");
		return $pageHtml;
	}
	require_once $file;

	$functionName = $prefix . $pluginName;
	if (!function_exists($functionName)) {
		fwrite(STDERR, "Warnung: Plugin-Einstiegsfunktion fehlt.\n");
		return $pageHtml;
	}

	$pluginSettings = SiteConfig::getPluginSettings();
	return call_user_func($functionName, $pluginSettings, $pageHtml);
}

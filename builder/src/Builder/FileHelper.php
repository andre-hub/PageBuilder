<?php

declare(strict_types=1);

/**
 * Liest den Verzeichnisinhalt einer Quellseite ein.
 *
 * Es zaehlt ausschliesslich, was tatsaechlich als Seite gebaut werden kann:
 * lesbare Dateien mit der Endung ".markdown". Unterverzeichnisse, versteckte
 * Dateien (fuehrender Punkt) und alle anderen Dateitypen werden ignoriert,
 * damit weder Namenskuerzung noch Zufallsfunde eine falsche/leere Seite
 * erzeugen (siehe Review-Befund 11).
 *
 * @return list<string> Basisnamen ohne Endung, alphabetisch sortiert
 */
function GetpathEntry($path) {
	if (!is_dir($path)) {
		return [];
	}
	$entryList = [];
	if ($handle = opendir($path)) {
		while (false !== ($pathEntry = readdir($handle))) {
			$entryList = ToPathList($entryList, $path, $pathEntry);
		}
		closedir($handle);
	}
	sort($entryList, SORT_STRING);
	return $entryList;
}

function ToPathList($entryList, $path, $pathEntry) {
	if (IsDirSpecials($pathEntry)) {
		return $entryList;
	}
	if (!IsMarkdownSource($path, $pathEntry)) {
		return $entryList;
	}
	$entryList[] = substr($pathEntry, 0, -\strlen(MarkdownExtension()));
	return $entryList;
}

function MarkdownExtension(): string {
	return '.markdown';
}

/**
 * Zaehlt eine Verzeichnisdatei als Markdown-Quelle, wenn sie sichtbar ist,
 * exakt auf ".markdown" endet und eine lesbare regulaere Datei ist. Ein
 * Symlink auf eine Datei zaehlt mit (is_file() folgt ihm), ein Symlink auf
 * ein Verzeichnis nicht.
 */
function IsMarkdownSource(string $path, string $pathEntry): bool {
	if (\strncmp($pathEntry, '.', 1) === 0) {
		return false;
	}
	$suffix = MarkdownExtension();
	if (\strlen($pathEntry) <= \strlen($suffix) || !\str_ends_with($pathEntry, $suffix)) {
		return false;
	}
	$full = rtrim($path, '/') . '/' . $pathEntry;
	if (is_link($full) && !IsPathWithin(CanonicalPath($full), PAGEBUILDER_SITE_ROOT)) {
		throw new RuntimeException();
	}
	return is_file($full) && is_readable($full);
}

/**
 * Laedt eine Template- oder Inhaltsdatei.
 *
 * @param string $buildSrcPath Verzeichnispfad (mit oder ohne abschließendem /)
 * @param string $filename     Dateiname ohne Erweiterung
 * @param string $type         'html' | 'markdown' (explizit statt Heuristik)
 *
 * @return string|false Inhalt, oder false wenn die Datei nicht lesbar ist
 */
function LoadTplFile($buildSrcPath, $filename, $type = 'html') {
	$ext      = ($type === 'markdown') ? MarkdownExtension() : '.html';
	$filename = FileWithExt($filename, $ext);
	$srcHtml  = FileRead($buildSrcPath, $filename);
	if ($srcHtml === false) {
		return false;
	}
	return trim($srcHtml);
}

function FileWithExt($filename, $ext) {
	if (substr_count($filename, $ext) === 0) {
		$filename .= $ext;
	}
	return $filename;
}

/**
 * @return string|false Dateiinhalt, oder false bei Lese-/Zugriffsfehler.
 *                       Ein Fehler wird nicht in einen leeren String
 *                       verwandelt (siehe Review-Befund 12).
 */
function FileRead($path, $filename) {
	if (!IsPathWithin(CanonicalPath($path . $filename), PAGEBUILDER_SITE_ROOT)) {
		throw new RuntimeException();
	}
	return file_get_contents($path . $filename);
}

/**
 * @return int|false Anzahl geschriebener Bytes, oder false bei Fehler.
 *                    Der Aufrufer muss den Rueckgabewert pruefen.
 */
function FileWrite($path, $filename, $fileContent, $add = false) {
	$flags  = LOCK_EX;
	$flags |= $add ? FILE_APPEND : 0;
	return file_put_contents($path . $filename, $fileContent, $flags);
}

/**
 * Legt das Ausgabeverzeichnis der Website an sicherem, geprueftem Ort neu an.
 *
 * $buildPagePath ist der aus der Konfiguration stammende, ggf. relative
 * Pfad; er wird gegen Quell-, Projekt- und Wurzelverzeichnis geprueft, bevor
 * ueberhaupt geloescht wird (siehe Review-Befund 3). Ein ungueltiges Ziel
 * bricht den Build mit einer Ausnahme ab, statt irgendetwas zu loeschen.
 *
 * @throws RuntimeException wenn das Ziel nicht sicher ist
 */
function BuildPageDir(string $buildPagePath, string $sourcePath): bool {
	$target = ResolveBuildTarget($buildPagePath, $sourcePath);
	return DirMake($target);
}

/**
 * Kanonisiert das Ausgabeziel und weist unsichere Konfigurationen zurueck:
 * leere Angabe, Wurzelverzeichnis, das Quellverzeichnis selbst sowie jede
 * Ueberschneidung mit dem Projektverzeichnis (Ziel ist das Projekt oder ein
 * Vorfahre davon - dann wuerde das Loeschen das ganze Projekt treffen).
 *
 * @throws RuntimeException
 */
function ResolveBuildTarget(string $buildPagePath, string $sourcePath): string {
	if (trim($buildPagePath) === '') {
		throw new RuntimeException('BuildPagePath ist leer.');
	}

	$target = CanonicalPath($buildPagePath);
	$site = PAGEBUILDER_SITE_ROOT;
	if (!IsPathWithin($target, $site) || $target === $site || is_link(rtrim($buildPagePath, '/'))) {
		throw new RuntimeException();
	}
	$relative = substr($target, strlen($site) + 1);
	foreach (explode('/', $relative) as $segment) {
		if (str_starts_with($segment, '.')) {
			throw new RuntimeException();
		}
	}
	$protected = [$sourcePath, PAGEBUILDER_SITE_DIR, $site . '/static', $site . '/pages', $site . '/sitemap.xml'];
	if (PAGEBUILDER_CORE_ROOT !== $site) {
		$protected[] = PAGEBUILDER_CORE_ROOT;
	}
	foreach ($protected as $path) {
		$path = CanonicalPath($path);
		if (IsPathWithin($target, $path) || IsPathWithin($path, $target)) {
			throw new RuntimeException();
		}
	}
	if ($target === '' || $target === '/') {
		throw new RuntimeException(
			"BuildPagePath '{$buildPagePath}' zeigt auf das Wurzelverzeichnis."
		);
	}

	$source = trim($sourcePath) === '' ? '' : CanonicalPath($sourcePath);
	if ($source !== '' && $target === $source) {
		throw new RuntimeException(
			"BuildPagePath '{$buildPagePath}' entspricht dem Quellverzeichnis '{$sourcePath}'."
		);
	}

	$projectRoot = CanonicalPath(PAGEBUILDER_PROJECT_ROOT);
	if ($projectRoot !== '' && IsPathWithin($projectRoot, $target)) {
		throw new RuntimeException(
			"BuildPagePath '{$buildPagePath}' ueberschneidet sich mit dem Projektverzeichnis."
		);
	}

	return $target;
}

/**
 * Loest . und .. lexikalisch auf, ohne dass der Pfad existieren muss, und
 * macht ihn absolut (relativ zum aktuellen Arbeitsverzeichnis). Existiert
 * der Pfad bereits, wird zusaetzlich realpath() angewendet: Das loest auch
 * Symlinks vollstaendig zu ihrem tatsaechlichen Ziel auf, unabhaengig von
 * einem abschliessenden Schraegstrich.
 */
function CanonicalPath(string $path): string {
	if ($path === '') {
		return '';
	}
	$absolute = \strncmp($path, '/', 1) === 0 ? $path : getcwd() . '/' . $path;
	$real     = realpath($absolute);
	if ($real !== false) {
		return $real === '/' ? '/' : rtrim($real, '/');
	}
	// Resolve existing ancestors too: realpath fails for a new output below
	// a symlink, but that must not allow writes outside the selected site.
	$tail = [];
	$ancestor = $absolute;
	while (!file_exists($ancestor) && !is_link($ancestor)) {
		$parent = dirname($ancestor);
		if ($parent === $ancestor) {
			throw new RuntimeException();
		}
		array_unshift($tail, basename($ancestor));
		$ancestor = $parent;
	}
	$resolved = realpath($ancestor);
	if ($resolved === false) {
		throw new RuntimeException();
	}
	$normalized = NormalizePath($resolved . '/' . implode('/', $tail));
	return $normalized === '/' ? '/' : rtrim($normalized, '/');
}

function ResolveSiteDirectory(string $path, bool $mustExist = true): string {
	$directory = CanonicalPath($path);
	if (($mustExist && !is_dir($directory)) || !IsPathWithin($directory, PAGEBUILDER_SITE_ROOT)) {
		throw new RuntimeException();
	}
	return $directory;
}

function NormalizePath(string $path): string {
	$path       = str_replace('\\', '/', $path);
	$isAbsolute = \strncmp($path, '/', 1) === 0;
	$segments   = explode('/', $path);
	$resolved   = [];
	foreach ($segments as $segment) {
		if ($segment === '' || $segment === '.') {
			continue;
		}
		if ($segment === '..') {
			if ($resolved !== [] && end($resolved) !== '..') {
				array_pop($resolved);
				continue;
			}
			if ($isAbsolute) {
				continue; // ueber die Wurzel hinaus geht nicht
			}
			$resolved[] = '..';
			continue;
		}
		$resolved[] = $segment;
	}
	$joined = implode('/', $resolved);
	if ($isAbsolute) {
		return '/' . $joined;
	}
	return $joined === '' ? '.' : $joined;
}

/**
 * true, wenn $path gleich $ancestor ist oder darunter liegt. Beide Pfade
 * muessen bereits kanonisiert (absolut, ohne abschliessenden Schraegstrich)
 * sein.
 */
function IsPathWithin(string $path, string $ancestor): bool {
	if ($path === $ancestor) {
		return true;
	}
	if ($ancestor === '/') {
		return true;
	}
	return \strncmp($path, $ancestor . '/', \strlen($ancestor) + 1) === 0;
}

/**
 * Loescht den angegebenen kanonischen Pfad rekursiv und legt ihn danach neu
 * an. Gibt false zurueck, sobald ein Schritt fehlschlaegt, statt den Fehler
 * zu verschlucken (siehe Review-Befund 12).
 */
function DirMake($path): bool {
	if (!DeleteDir($path)) {
		return false;
	}
	if (is_dir($path)) {
		return true;
	}
	return mkdir($path, 0755, true);
}

/**
 * Loescht rekursiv. Die Symlink-Pruefung erfolgt ausdruecklich vor jeder
 * is_dir()-Pruefung und auf dem von einem abschliessenden Schraegstrich
 * befreiten Pfad: Mit Schraegstrich liefert is_link() faelschlich false und
 * is_dir() true, wodurch ein Verzeichnis-Symlink faelschlich durchlaufen und
 * sein Ziel geleert wuerde (siehe Review-Befund 3).
 */
function DeleteDir($path): bool {
	$trimmed = rtrim($path, '/');
	if ($trimmed === '') {
		return false;
	}
	if (is_link($trimmed) || is_file($trimmed)) {
		return unlink($trimmed);
	}
	if (!is_dir($trimmed)) {
		return true;
	}
	$dirPath = $trimmed . '/';
	$items   = scandir($dirPath);
	if ($items === false) {
		return false;
	}
	foreach ($items as $item) {
		if (IsDirSpecials($item)) {
			continue;
		}
		if (!DeleteDir($dirPath . $item)) {
			return false;
		}
	}
	return rmdir($dirPath);
}

function IsDirSpecials($path): bool {
	return $path === '.' || $path === '..' || $path === '';
}

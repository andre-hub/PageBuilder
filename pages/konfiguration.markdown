## Konfiguration

Jede Site hat genau eine Konfigurationsdatei, `builder/SiteConfig.php`. Sie
definiert die Klasse `SiteConfig`; deren Methode `load()` liefert das
Einstellungsarray. Eigene Werte werden innerhalb dieser Methode gepflegt.
Die Klasse sowie `loadPlugins()` und `getPluginSettings()` bleiben erhalten.
Ein alleinstehendes `return`-Array als kompletter Dateiinhalt genügt nicht.

### Grundeinstellungen

Der folgende Ausschnitt ersetzt nur das Array innerhalb von `load()`:

```php
return [
    'SiteName'       => 'Meine Site',
    'SiteUrl'        => 'example.org',
    'StaticPath'     => '../static/',
    'BuildPagePath'  => '../html/',
    'SourcePath'     => '../pages/',
    'MarkdownParser' => 'CommonMark',
    'VendorLib'      => 'vendor',
    'Theme'          => 'default',
    'ThemePath'      => '../static/themes/',
    'Lang'           => 'de',
];
```

- `SiteName` erscheint im Titel jeder Seite und im Kopf-/Fußbereich.
- `SiteUrl` enthält den Hostnamen ohne Schema oder abschließenden
  Schrägstrich, etwa `example.org`. Menü und Template verwenden ihn als
  HTTPS-Basisadresse; auch die automatische Sitemap verwendet diese Basisadresse.
  Markdown-Links werden dadurch nicht allgemein umgeschrieben.
- `StaticPath`, `BuildPagePath` und `SourcePath` sind relative Pfade,
  ausgehend von `builder/` der ausgewählten Website, auch bei einem gemeinsamen
  Core mit `--site`: `SiteConfig::load()` setzt dieses
  Arbeitsverzeichnis ausdrücklich. `BuildPagePath` wird bei jedem Build
  ersetzt und darf keine Quellen oder eigenen Dateien enthalten.
- `Theme` benennt den Ordner unter `static/themes/`, aus dem
  `tokens.css` und `theme.css` geladen werden.
- `ThemePath` bezeichnet den lokalen Theme-Pfad. Das Demo-Template
  verwendet für öffentliche Theme-URLs fest `themes/<name>/`; ein anderer
  lokaler Pfad ändert dieses URL-Schema nicht automatisch.
- `MarkdownParser` unterstützt aktuell ausschließlich `CommonMark`.
- `VendorLib` benennt das Composer-Verzeichnis, in der Demo `vendor`.
- `Lang` setzt das `lang`-Attribut auf `<html>` und muss zur Sprache der
  Inhalte passen.

### Abbildungen (`Figure`)

Auflösungen, Bildformat und die verfügbaren Blockarten für `@bild` und
`@trenner` stehen an einer Stelle, statt in jedem Artikel wiederholt zu
werden:

```php
'Figure' => [
    'basePath'  => '/img/',
    'extension' => 'webp',
    'variants'  => ['sml' => 600, 'lrg' => 1200],
    'default'   => 'lrg',
    'sizes'     => '100vw',
    'kinds'     => [
        'bild'    => ['class' => 'illustration', 'width' => 1200, 'height' => 800, 'caption' => true],
        'trenner' => ['class' => 'illustration illustration--trenner', 'width' => 1200, 'height' => 400, 'caption' => false],
    ],
    'captionSuffix' => '',
    'loading' => 'lazy',
],
```

`variants` ist die Liste der Auflösungskürzel mit ihrer jeweiligen Breite
in Pixeln, in der Reihenfolge, in der sie im `srcset` erscheinen sollen.
`default` bestimmt, welche Fassung im schlichten `src`-Attribut steht —
wichtig für Browser, die kein `srcset` auswerten. `kinds` ordnet jedem
Schlüsselwort nach dem `@` eine CSS-Klasse, feste Platzhaltermaße für
`width`/`height` und die Frage zu, ob eine Bildunterschrift ausgegeben
wird. Wer eine dritte Blockart braucht, ergänzt hier einen weiteren
Eintrag — im Markdown-Text steht dann einfach `@neuername …`.
`captionSuffix` ist optionales, vertrauenswürdiges HTML nach einer
vorhandenen Bildunterschrift. Es ist kein Feld für fremde Nutzereingaben.
Bildvarianten müssen bereits existieren; PageBuilder erzeugt sie nicht.

### Inhaltsverzeichnis (`TocMinLength`, `TocMinSections`, `TocLabel`)

```php
'TocMinLength'   => 4500,
'TocMinSections' => 4,
'TocLabel'       => 'Inhalt',
```

`TocMinLength` ist die Mindestlänge des verbleibenden Markdown-Quelltexts
nach der Titelübernahme in **Bytes**, nicht Unicode-Zeichen,
`TocMinSections` die Mindestanzahl an Überschriften zweiter und dritter
Ordnung. Nur wenn eine Seite beide Werte erreicht, entsteht automatisch
ein Inhaltsverzeichnis (Details dazu unter [Syntax](/syntax)).
`TocLabel` ist die sichtbare Beschriftung des erzeugten Verzeichnisses.
Fehlt `TocMinLength`, verwendet der Renderer 6000 Bytes als Fallback.
Ein ausdrücklich gesetztes `[TOC]` umgeht die automatischen Schwellen.

### Navigation (`NavOrder`, `NavLabels`, `NavHidden`, `PageTitles`)

Ohne weitere Angaben erscheinen alle Seiten alphabetisch nach Dateiname
in der Navigation. Für die Standardbeschriftung werden Bindestriche und
Unterstriche im Dateinamen zu Leerzeichen; der erste Buchstabe wird groß. Vier optionale
Einstellungen verfeinern das:

- `NavOrder` — bevorzugte Reihenfolge der Menüpunkte; nicht genannte
  Seiten folgen natürlich-alphabetisch.
- `NavLabels` — Klartext-Beschriftung je Seite, unabhängig vom
  Dateinamen.
- `NavHidden` — Dateinamen oder Muster mit `*`, die zwar als Seite
  gebaut werden, aber nicht im Menü erscheinen.
- `PageTitles` — ein Seitentitel für Seiten, deren erste Überschrift
  nicht als Titel taugt oder die keine eigene erste Überschrift haben.

Eine explizite Nennung in `NavOrder` übersteuert `NavHidden`. Die Slugs
`agb`, `impressum` und `imprint` bleiben dennoch aus der Hauptnavigation
herausgefiltert. Ausgeblendete Seiten werden weiterhin gebaut und in die
automatisch erzeugte Sitemap aufgenommen; diese Option ist kein Zugriffsschutz.

Für Titel gilt: `PageTitles` vor erster ATX-Überschrift (`#` bis `######`)
als erster nichtleerer Zeile vor aufbereitetem Dateinamen. Nur eine
als Titel übernommene Überschrift wird aus dem Text entfernt; bei einem
`PageTitles`-Eintrag bleibt der Markdown-Quelltext unverändert.

```php
'NavOrder'  => ['index', 'funktionen', 'installation'],
'NavLabels' => ['index' => 'Start'],
'NavHidden' => ['entwurf-*'],
'PageTitles' => ['index' => 'Meine Site'],
```

### Plugins: derzeit nicht regulär aktivierbar

`SiteConfig::loadPlugins()` ist als Freigabeliste vorgesehen,
`SiteConfig::getPluginSettings()` liefert Plugin-Einstellungen. Die Hooks
sehen Dateien `builder/plugins/plugin_<name>.php` und Funktionen
`Plugin<Name>($settings, $pageHtml)` vor, die das bearbeitete HTML
zurückgeben.

Im aktuellen Stand verwendet die Plugin-Suche jedoch den auf
Markdown-Dateien beschränkten Dateifilter und findet normale PHP-Plugins
nicht. Die Demo-Liste ist zudem leer. Das Hinzufügen eines Namens zur
Freigabeliste genügt deshalb derzeit nicht; die Hooks sind keine als
funktionsfähig zugesicherte Plugin-Schnittstelle.

### Vertrauensgrenze

SiteConfig und Plugins sind ausführbarer PHP-Code. Auch Template und
Markdown müssen aus vertrauenswürdiger eigener Pflege stammen; Roh-HTML
ist im Markdown nicht generell gesperrt. Der Builder bereinigt fremde Inhalte nicht umfassend. Eine Übersicht aller Fähigkeiten und
Grenzen steht unter [Funktionen](/funktionen).

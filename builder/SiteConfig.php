<?php

declare(strict_types=1);

/**
 * Beispielhafte Site-Konfiguration von PageBuilder.
 *
 * Diese Datei ist die Konfiguration der PageBuilder-Demo-Site selbst; wer
 * PageBuilder fuer ein eigenes Projekt einsetzt, ersetzt sie durch eine
 * eigene Fassung mit denselben Schluesseln. Jeder Schluessel, den der
 * Generator kennt, ist unten mit einem kurzen Kommentar aufgefuehrt.
 */

define('THEME_API_VERSION', '1.0');

class SiteConfig
{
    public static function load(): array
    {
        // Alle Pfade unten sind relativ zu diesem Verzeichnis (builder/)
        // gemeint; das Arbeitsverzeichnis wird deshalb hierher gesetzt,
        // unabhaengig davon, von wo der Build gestartet wurde.
        chdir(__DIR__);

        return [
            // Anzeigename der Website, erscheint u. a. im Footer und im
            // {SITENAME}-Platzhalter des Templates.
            'SiteName' => 'Page Builder',

            // Hostname der Website ohne Schema, wird zu einer absoluten
            // https-URL ergaenzt (siehe MakeUrl()) und fuer alle internen
            // Links und die kanonische Adresse jeder Seite verwendet.
            'SiteUrl' => 'pagebuilder.projekt-matrix.de',

            // Verzeichnis mit den statischen Assets (Templates, CSS, Bilder,
            // Themes), relativ zu diesem Verzeichnis.
            'StaticPath' => '../static/',

            // Ausgabeverzeichnis fuer die erzeugten HTML-Dateien. Wird vor
            // jedem Build geleert und neu angelegt - darf deshalb niemals
            // leer, das Wurzel-, Quell- oder Projektverzeichnis sein
            // (wird von ResolveBuildTarget() erzwungen).
            'BuildPagePath' => '../html/',

            // Verzeichnis mit den Markdown-Quellseiten. Nur Dateien mit der
            // Endung ".markdown" werden als Seite erkannt.
            'SourcePath' => '../pages/',

            // Aktuell wird ausschliesslich CommonMark unterstuetzt; der
            // Schluessel existiert fuer eine spaetere Parserwahl.
            'MarkdownParser' => 'CommonMark',

            // Name des vom Composer-Autoloader verwalteten vendor-Ordners.
            'VendorLib' => 'vendor',

            // Aktives Theme: Name eines Unterordners unter ThemePath, der
            // tokens.css und theme.css bereitstellt.
            'Theme' => 'default',

            // Verzeichnis, das die Theme-Unterordner enthaelt.
            'ThemePath' => '../static/themes/',

            // Sprache der Website, landet im {LANG}-Attribut von <html>.
            'Lang' => 'de',

            // Einstellungen fuer die eigene Abbildungs-Blocksyntax
            // "@schluesselwort name | Alt | Bildunterschrift" (siehe
            // PageBuilder\Markdown\Figure). Ohne diesen Schluessel gelten
            // die Defaults aus FigureSettings.
            'Figure' => [
                // Pfadpraefix, unter dem die Bilddateien liegen.
                'basePath' => '/img/',
                // Dateiendung aller Bildvarianten, ohne Punkt.
                'extension' => 'webp',
                // Namenszusatz => Breite in Pixeln, ergibt das srcset.
                'variants' => ['sml' => 600, 'mid' => 900, 'lrg' => 1200, 'xl' => 1536],
                // Namenszusatz der Fassung, die im src-Attribut steht.
                'default' => 'lrg',
                // Wert des sizes-Attributs fuer die responsiven Bilder.
                'sizes' => '(max-width: 767px) 100vw, 68ch',
                // Schluesselwort im Text => Auszeichnung und Maße der
                // Abbildung. Mehrere Arten sind moeglich, etwa fuer Bilder
                // mit und ohne Bildunterschrift.
                'kinds' => [
                    'bild'    => ['class' => 'illustration', 'width' => 1200, 'height' => 800, 'caption' => true],
                    'trenner' => ['class' => 'illustration illustration--trenner', 'width' => 1200, 'height' => 400, 'caption' => false],
                ],
                // Optionales HTML, das jeder Bildunterschrift angehaengt
                // wird (z. B. ein Bildnachweis). Vertrauenswuerdige
                // Konfiguration, kein Nutzertextfeld.
                'captionSuffix' => '',
                // Wert des loading-Attributs.
                'loading' => 'lazy',
            ],

            // Ab wieviel Zeichen Quelltext ein automatisches
            // Inhaltsverzeichnis eingefuegt wird (0 = immer, sofern
            // TocMinSections erreicht ist).
            'TocMinLength' => 4500,

            // Mindestanzahl an ## / ###-Abschnitten fuer ein automatisches
            // Inhaltsverzeichnis.
            'TocMinSections' => 4,

            // Beschriftung des automatisch eingefuegten Inhaltsverzeichnisses.
            'TocLabel' => 'Inhalt',

            // Reihenfolge der Menuepunkte. Seiten, die hier nicht auftauchen,
            // haengen alphabetisch hinten an. Werte sind Seiten-Slugs
            // (Dateiname ohne ".markdown").
            'NavOrder' => [
                'index',
                'imprint',
                'agb',
            ],

            // Menuebeschriftung je Slug; ohne Eintrag wird der Dateiname mit
            // Bindestrichen als Leerzeichen und grossem Anfangsbuchstaben
            // verwendet.
            'NavLabels' => [
                'index'   => 'Start',
                'imprint' => 'Impressum',
                'agb'     => 'AGB',
            ],

            // Seiten, die erreichbar bleiben und in der Sitemap stehen,
            // aber nicht im Menue erscheinen sollen. Exakter Slug oder
            // Muster mit * als Platzhalter, etwa 'entwurf-*'. "_why" ist ein
            // Beispiel dafuer: die Seite existiert, gehoert aber nicht ins
            // Hauptmenue.
            'NavHidden' => ['_why'],

            // Titel fuer Seiten, deren erste Ueberschrift nicht als
            // Seitentitel taugt oder die keine haben. Ohne Eintrag gilt die
            // erste Ueberschrift der Quelldatei, sonst der Dateiname.
            'PageTitles' => [],
        ];
    }

    /**
     * Freigabeliste der Plugins unter builder/plugins/, die tatsaechlich
     * geladen werden sollen. Ein im Verzeichnis gefundenes Plugin ohne
     * Eintrag hier bleibt inaktiv. Beispiel: ['share'] aktiviert
     * builder/plugins/plugin_share.php (Funktion PluginShare()).
     *
     * @return list<string>
     */
    public static function loadPlugins(): array
    {
        return [];
    }

    /**
     * Konfigurationswerte, die an aktive Plugins uebergeben werden.
     *
     * @return list<array<string,string>>
     */
    public static function getPluginSettings(): array
    {
        return [];
    }
}

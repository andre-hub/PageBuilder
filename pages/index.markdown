## Ein Static-Site-Generator ohne Umweg

PageBuilder liest Markdown-Dateien, setzt ihren Inhalt in ein HTML-Template ein und erzeugt fertige HTML-Seiten. Ein PHP-Aufruf übernimmt den Build; ein JavaScript-Bundler oder eine Datenbank ist nicht nötig. Die fertigen Seiten und ihre statischen Assets lassen sich ohne PHP ausliefern.

Diese Website ist Demo und Referenz des Generators. Sie wird mit PageBuilder selbst gebaut und dokumentiert seinen tatsächlich vorhandenen Funktionsumfang.

### Was PageBuilder bietet

- PHP 8.4 bis kleiner 9.0 mit `mbstring` und per Composer installierten Abhängigkeiten zum Bauen
- CommonMark-Inhalte mit Tabellen und Überschriften-Sprungmarken
- Eigene Abbildungsblöcke mit responsiven Bildreferenzen
- Automatische oder ausdrücklich platzierte Inhaltsverzeichnisse
- Konfigurierbare Titel und Navigation
- CSS-Themes mit Hell-/Dunkelschema und lokalen Schriften
- Statische HTML-Ausgabe und automatische Sitemap mit einem PHP-Builderaufruf

### Dokumentation

- [Alle Funktionen und Grenzen](/funktionen) — vollständiger Überblick, einschließlich nicht implementierter Fähigkeiten
- [Syntax](/syntax) — Markdown, Tabellen, Abbildungen und Inhaltsverzeichnisse
- [Konfiguration](/konfiguration) — Einstellungen, Navigation, Themes und Plugin-Status
- [Installation](/installation) — Voraussetzungen, PHP-Build und Auslieferungsgrenze

Barrierefreiheitsmaßnahmen sind eingebaut; eine vollständige WCAG-Konformität ist damit nicht nachgewiesen. Für fremde, unvertrauenswürdige Inhalte ist der Generator ohne zusätzliche Schutzmaßnahmen nicht gedacht.

### Beispielseiten

[AGB](/agb) ist ein Platzhalter. [Kontakt](/imprint) verweist auf die GitHub-Issues des Projekts. Für eine eigene Site müssen benötigte Rechtstexte unabhängig erstellt und geprüft werden.

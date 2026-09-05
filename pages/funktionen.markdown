## Funktionen und Grenzen

PageBuilder ist ein dateibasierter Static-Site-Generator für überschaubare Websites mit selbst gepflegten Inhalten. Diese Übersicht beschreibt den vorhandenen Code, nicht geplante Erweiterungen. Voraussetzungen und Befehle stehen unter [Installation](/installation), konkrete Einstellungen unter [Konfiguration](/konfiguration).

### Seiten aus Markdown bauen

Der Builder liest ausschließlich `.markdown`-Dateien direkt aus `SourcePath`. Jede davon erzeugt eine gleichnamige `.html`-Datei in `BuildPagePath`; Unterverzeichnisse werden nicht rekursiv als Seitenbaum verarbeitet. Als Parser dient `league/commonmark` mit CommonMark-Grundelementen sowie gezielt aktivierten Erweiterungen für Tabellen, Überschriftenlinks, Inhaltsverzeichnisse und Abbildungen. Nicht jede beliebige Markdown-Erweiterung ist damit unterstützt.

Das HTML-Gerüst kommt aus `StaticPath/html/index.html`. Das Ausgabeverzeichnis wird vor jedem Build ersetzt. Inhalte, Templates und eigene Anpassungen müssen deshalb außerhalb dieses Verzeichnisses gepflegt werden. Der Builder meldet erkannte Buildfehler über einen Exitcode ungleich null.

### Titel und Navigation

Für den Seitentitel gilt diese Reihenfolge:

1. Ein expliziter Eintrag in `PageTitles`.
2. Eine ATX-Überschrift mit einem bis sechs `#` als erste nichtleere Zeile der Datei.
3. Der aufbereitete Dateiname.

Wird die erste Überschrift als Titel übernommen, entfernt der Builder sie aus dem Fließtext. Bei einem `PageTitles`-Eintrag bleibt der Quelltext hingegen unverändert. Das Template setzt den ermittelten Titel in Seitentitel und H1 ein.

Die Hauptnavigation entsteht aus den Quelldateien. `NavOrder` legt die bevorzugte Reihenfolge fest; übrige Seiten folgen natürlich-alphabetisch. `NavLabels` liefert individuelle Beschriftungen. Ohne Eintrag werden Bindestriche und Unterstriche durch Leerzeichen ersetzt und der erste Buchstabe großgeschrieben. Die aktuelle Seite erhält `aria-current="page"`.

`NavHidden` blendet exakte Slugs oder Muster mit `*` aus dem Menü aus. Eine explizite Nennung in `NavOrder` übersteuert diese Ausblendung. Die Slugs `agb`, `impressum` und `imprint` werden unabhängig davon nicht in die Hauptnavigation aufgenommen; das Demo-Template verlinkt AGB und Kontakt separat im Footer. Ausblendung ist kein Zugriffsschutz: die HTML-Seiten werden weiterhin gebaut und in die automatisch erzeugte Sitemap aufgenommen.

`index`, `home` und `startseite` werden in Navigation und Sitemap auf `/` abgebildet. Andere Slugs erscheinen als URL-Pfad ohne `.html`. Das passende Webserver-Routing ist eine zusätzliche Betriebsaufgabe; der Generator richtet es nicht selbst ein. Pro Site sollte nur eine Startseitenvariante verwendet werden.

### Tabellen und Sprungmarken

Markdown-Tabellen werden als HTML-Tabellen ausgegeben. Ein fokussierbarer Bereich mit der Klasse `tabelle`, `role="region"` und `tabindex="0"` umschließt sie; das CSS kann breite Tabellen innerhalb dieses Bereichs scrollen lassen, statt die gesamte Seite zu verbreitern.

Überschriften H2 bis H4 erhalten Sprungmarken über die CommonMark-Permalink-Erweiterung. Solche Abschnittslinks benötigen kein JavaScript. Gute Überschriften und sinnvolle Tabelleninhalte bleiben Aufgabe der Redaktion.

### Inhaltsverzeichnisse

Ein ausdrücklich als eigener Absatz gesetztes `[TOC]` erzeugt das Verzeichnis an dieser Stelle, unabhängig von den Schwellen für die automatische Einfügung. Das Verzeichnis umfasst H2 und H3 und erscheint als geordnete Liste in einem beschrifteten Navigationsbereich. `TocLabel` bestimmt dessen Beschriftung.

Ohne ausdrücklichen Platzhalter fügt der Builder bei ausreichend langem und gegliedertem Text selbst ein Verzeichnis ein: Die Demo setzt mindestens 4500 Bytes Quelltext und vier H2-/H3-Abschnitte voraus. Die Prüfung erfolgt nach der Titelübernahme. Bytes sind bei UTF-8 nicht gleichbedeutend mit sichtbaren Zeichen. Fehlt `TocMinLength` ganz, beträgt der Renderer-Fallback 6000 Bytes.

Die automatische Position liegt hinter dem ersten geeigneten Einleitungsblock; direkt anschließende `@`-Abbildungsblöcke bleiben vor dem Verzeichnis. Das ist eine quelltextbasierte Heuristik, kein vollständiges semantisches Verständnis der Einleitung. Auch das bloße Vorkommen von `[TOC]` in einem Codebeispiel verhindert derzeit die automatische Einfügung. Details und Beispiele stehen unter [Syntax](/syntax).

### Abbildungen und responsive Bildreferenzen

`@bild` und `@trenner` sind eigene Blockarten. Aus Grundname, Alternativtext und optionaler Bildunterschrift entsteht eine `figure` mit `img`; Bildunterschriften werden nur bei entsprechend konfigurierten Arten und nichtleerem Text ausgegeben.

Die zentrale `Figure`-Konfiguration legt Bildpfad, Dateiendung, Variantenbreiten, Standardvariante, `sizes`, `loading`, CSS-Klassen sowie Breite und Höhe fest. Daraus entstehen `src` und `srcset`. In der Demo heißen die Varianten `sml`, `mid`, `lrg` und `xl`; Standard ist `lrg`, geladen wird standardmäßig mit `loading="lazy"`.

Weitere Blockarten lassen sich in `Figure.kinds` definieren. `captionSuffix` kann einen gemeinsamen HTML-Zusatz an vorhandene Bildunterschriften anhängen und ist deshalb ausschließlich vertrauenswürdige Konfiguration.

Der Generator skaliert oder konvertiert keine Bilder, er prüft auch nicht jede erzeugte Bildreferenz auf Existenz. Die Varianten müssen separat hergestellt und ausgeliefert werden. Ein fehlender Alternativtext führt technisch zu `alt=""`, nicht zum Buildfehler; aussagekräftige Alternativtexte für inhaltliche Bilder müssen redaktionell sichergestellt werden.

### Templates und Themes

Das Template unterstützt genau diese acht Platzhalter:

| Platzhalter | Inhalt |
|---|---|
| <code>{<span>NAV</span>}</code> | Generierte Listeneinträge der Hauptnavigation |
| <code>{<span>SITETITLE</span>}</code> | Ermittelter Seitentitel |
| <code>{<span>TEXT</span>}</code> | Gerenderter Markdown-Inhalt |
| <code>{<span>SITENAME</span>}</code> | Anzeigename aus der Konfiguration |
| <code>{<span>SITEURL</span>}</code> | Basis-URL mit abschließendem Schrägstrich |
| <code>{<span>LANG</span>}</code> | Sprachkennzeichnung des Dokuments |
| <code>{<span>SKIPLINK</span>}</code> | Sprunglink zu `main-content` |
| <code>{<span>THEME_NAME</span>}</code> | Name des aktiven Themes |

Die Theme-API hat Version 1.0. Das Demo-Template lädt zuerst Normalize, dann die Basisregeln, danach `tokens.css` und `theme.css` des gewählten Themes. CSS Custom Properties trennen Designwerte von Komponentenregeln. Die Basis liefert Defaults, ersetzt aber kein fehlendes Theme und keine fehlende Asset-Datei.

Das Default-Theme bringt ein responsives Layout, lokale Schriften, helle und dunkle Farbwerte über `prefers-color-scheme`, Fokusmarkierungen und Regeln für `prefers-reduced-motion` mit. Das Farbschema folgt der Systemeinstellung; ein interaktiver Theme-Umschalter ist nicht Bestandteil des Generators.

### Barrierefreiheitsmaßnahmen und JavaScript-Grenze

Das Demo-Template verwendet semantische Bereiche, eine Sprachangabe, einen Skip-Link und eine gekennzeichnete Hauptnavigation. Hinzu kommen Fokusregeln, die Markierung der aktuellen Seite, scrollbare Tabellen und ein beschriftetes Inhaltsverzeichnis. Diese Maßnahmen erleichtern die Nutzung, belegen aber keine vollständige WCAG-2.2-AA-Konformität. Tastaturbedienung, Screenreader-Verhalten, Kontraste, Zoom und die Qualität eigener Inhalte und Themes müssen gesondert geprüft werden.

Für die vorgesehene statische Darstellung wird kein JavaScript benötigt. Der Builder ist jedoch kein HTML-Sanitizer und schließt aktive HTML-Inhalte nicht generell aus. Roh-HTML aus Markdown ist nicht generell deaktiviert. Nur vertrauenswürdige Quellen, Templates und Konfiguration verwenden.

### HTML und Sitemap mit einem PHP-Aufruf

Nach der Installation der Composer-Abhängigkeiten baut `php builder/index.php`
aus der Repository-Wurzel die HTML-Seiten und erzeugt automatisch
`sitemap.xml` in derselben Repository-Wurzel. Ein separates Sitemap-Werkzeug
oder eine Shell-Skriptkette wird nicht benötigt.

Grundlage der Sitemap sind die Quelldateien einschließlich im Menü
ausgeblendeter Seiten; deren Änderungsdatum wird als `lastmod` verwendet.
Die Sitemap muss zusammen mit den Seiten ausgeliefert werden. Ausblendung
im Menü macht Inhalte weder privat noch unsichtbar für Suchmaschinen.

Der Builder versieht CSS-Links nicht automatisch mit Inhalts-Hashes und
führt keine umfassende Ausgabeprüfung durch. Ob Links und Bilddateien
funktionieren und die Seite gut bedienbar ist, muss vor der Veröffentlichung
unabhängig geprüft werden.

### Plugins: vorhandener Ansatz, derzeitige Einschränkung

`SiteConfig::loadPlugins()` und `SiteConfig::getPluginSettings()` sowie Loader-Hooks und ein Share-Beispiel sind vorhanden. Vorgesehen sind PHP-Dateien `builder/plugins/plugin_<name>.php` und Funktionen mit Einstellungen und Seiten-HTML als Argumenten.

Der aktuelle Suchpfad verwendet jedoch die auf Markdown-Dateien begrenzte Dateisuche. Reguläre PHP-Plugin-Dateien werden damit nicht entdeckt. Die Demo-Freigabeliste ist zudem leer. Eine bloße Freigabe eines Plugin-Namens reicht im aktuellen Stand nicht aus; Plugins sind deshalb keine als funktionsfähig zugesicherte Erweiterungsmöglichkeit.

### Bewusste Grenzen und Auslieferung

PageBuilder ist kein CMS mit Browser-Editor, Benutzerverwaltung oder Datenbank. Er enthält keinen Suchindex, keinen rekursiven Inhaltsbaum, keinen JavaScript-Bundler und keine automatische Bildproduktion. Konfiguration ist PHP-Code und nicht für unkontrollierte Eingaben gedacht.

Die fertige Website besteht aus HTML und den benötigten statischen Assets. Nur `html/` zu kopieren reicht für das Default-Template nicht. Umgekehrt darf nicht ungeprüft das gesamte Repository öffentlich lesbar werden: Backend, Abhängigkeiten und Quellen gehören nicht in den öffentlichen Dokumentenstamm. Routing, Dateiauswahl und Zugriffsschutz müssen vor der Veröffentlichung geprüft werden.

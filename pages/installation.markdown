## Installation

### Voraussetzungen

- PHP 8.4 bis kleiner 9.0 gemäß Composer-Anforderung `^8.4`
- PHP-Erweiterung `mbstring`
- Composer zur Installation der in `builder/composer.lock` festgelegten Abhängigkeiten, insbesondere `league/commonmark`

Der Build läuft auf der PHP-Kommandozeile und benötigt keinen laufenden Webserver. Zur Auslieferung der fertigen HTML-Seiten und Assets genügt ein Webserver ohne PHP-Laufzeit.

### Abhängigkeiten installieren

Alle folgenden Befehle werden aus der Repository-Wurzel ausgeführt. Der Composer-Aufruf lädt Pakete aus den konfigurierten Paketquellen:

```sh
composer install --working-dir=builder --no-dev --optimize-autoloader
```

### Site bauen

```sh
php builder/index.php
```

Der Aufruf liest die `.markdown`-Dateien direkt aus `SourcePath`, setzt sie in `static/html/index.html` ein und schreibt die Ausgabe nach `BuildPagePath`, in der Demo `html/`. Unterverzeichnisse werden nicht rekursiv verarbeitet. Erkannte Buildfehler führen zu einem Exitcode ungleich null.

**Das Ausgabeverzeichnis wird bei jedem Build ersetzt.** Eigene Änderungen dort gehen verloren. Inhalte gehören nach `pages/`, Designänderungen in Template und Theme; das Ausgabeziel darf niemals wertvolle Quellen oder andere eigene Dateien enthalten.

### Automatische Sitemap

Derselbe Builderaufruf erzeugt neben den HTML-Seiten automatisch
`sitemap.xml` in der Repository-Wurzel. Sie enthält auch Seiten, die durch
`NavHidden` aus dem Menü ausgeblendet sind. Ein zweiter Aufruf oder ein
separates Sitemap-Werkzeug ist nicht erforderlich.

Die fertigen Seiten vor der Veröffentlichung im Browser kontrollieren:
Navigation, Bilddateien, Inhalte, Tastaturbedienung und Barrierefreiheit
werden nicht vollständig vom Builder geprüft. Eine automatische
CSS-Hashversionierung oder umfassende Ausgabeprüfung gehört nicht zum
Programm.

### Eigene Site einrichten

1. Die Werte in `SiteConfig::load()` innerhalb von `builder/SiteConfig.php` anpassen; die Klasse und ihre Methoden erhalten. Siehe [Konfiguration](/konfiguration).
2. Eigene vertrauenswürdige Inhalte als `.markdown`-Dateien direkt im Quellverzeichnis pflegen.
3. `static/html/index.html` und das Theme unter `static/themes/<name>/` anpassen; benötigte Assets bereitstellen.
4. Bildvarianten separat erzeugen. PageBuilder referenziert sie, erzeugt oder konvertiert sie aber nicht.
5. Mit dem einen PHP-Aufruf HTML und Sitemap bauen und die fertige Site im Browser sowie mit Tastatur kontrollieren.

Bei Backend-Updates die eigene SiteConfig, Inhalte, Templates und Themes erhalten.

### Einen Core für mehrere Websites verwenden

Den Builder und seine Composer-Abhängigkeiten nur im PageBuilder-Verzeichnis
pflegen. Separate Websites behalten ihre `builder/SiteConfig.php`, Inhalte,
Templates und Assets; sie benötigen keine Kopie von `builder/src/`.
Vom PageBuilder-Verzeichnis aus baut derselbe PHP-Einstieg eine solche Website:

```sh
php builder/index.php --site /pfad/zur/website
```

Ein relativer Site-Pfad bezieht sich auf das Aufrufverzeichnis. Die Pfade in
der SiteConfig bleiben relativ zum `builder/` der ausgewählten Website.
HTML und Sitemap werden dort erzeugt, nicht in der Core-Demo.

### Statisch ausliefern

Öffentlich erreichbar sein dürfen nur erzeugte HTML-Seiten sowie die erforderlichen Assets aus `static/css/`, `static/fonts/`, `static/themes/` und `static/img/`, ergänzt um Sitemap, Robots-Datei und verwendete Favicons. Die Pfade müssen zu den URLs im Template passen.

Für einen Build auf dem Zielsystem können gemeinsamer Builder und Site-Dateien
als vollständiger, eigenständig ausführbarer Projektbaum übertragen werden.
Dort werden die gemeinsamen Composer-Abhängigkeiten installiert und
`php builder/index.php` ausgeführt. Die Bauquellen müssen gegen HTTP-Zugriff
gesperrt bleiben. Online-Notfallfixes vor dem nächsten Deployment in das
zuständige Core- oder Site-Repository zurückführen, sonst werden sie überschrieben.

Das Routing muss `/` auf die Startseite, Seiten-Slugs auf ihre HTML-Dateien und Asset-URLs auf die jeweiligen Dateien abbilden. Die mitgelieferte `.htaccess` ist eine Apache-2.4-Konfiguration für den Domain-DocumentRoot und genau `html/index.html` als Startseite. Nginx, andere Server und eine abweichende Startseite benötigen passende eigene Routingregeln. Die Wirksamkeit der Apache-Regeln hängt von der Serverkonfiguration ab und muss in der Zielumgebung geprüft werden.

Nicht ungeprüft die Repository-Wurzel veröffentlichen: Backend, Composer-Abhängigkeiten und Quellen dürfen nicht öffentlich lesbar werden. Dateiauswahl, Routing und Zugriffsschutz vor jeder Veröffentlichung gesondert prüfen.

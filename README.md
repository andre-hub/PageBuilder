# PageBuilder

PHP-Static-Site-Generator für Markdown-Seiten mit Themes, responsiven Bildreferenzen und automatischer Sitemap. Keine Datenbank und kein JavaScript-Bundler.

## Bauen

Voraussetzungen: PHP 8.4+, `mbstring` und Composer.

```sh
composer install --working-dir=builder --no-dev
php builder/index.php
```

Erzeugt `html/` und `sitemap.xml`. Inhalte liegen in `pages/`, Einstellungen in `builder/SiteConfig.php`. Eine separate Website baut derselbe Core mit `php builder/index.php --site /pfad/zur/website`.

[Demo](https://pagebuilder.projekt-matrix.de/) · [Funktionen](https://pagebuilder.projekt-matrix.de/funktionen) · [GitHub Issues](https://github.com/andre-hub/PageBuilder/issues)

## Lizenz

Copyright (C) 2012–2026 André Grötschel. GNU GPL v3 oder später.  
Siehe <https://www.gnu.org/licenses/>.

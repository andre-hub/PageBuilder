# Page Builder - Static Site Builder

Ein moderner, PHP 8-kompatibler Static Site Generator für beliebige Webseiten.

## Features

- PHP 8 kompatibel mit League/CommonMark
- Composer-basierte Dependency-Verwaltung
- PSR-4 Autoloading
- Responsive Design
- Plugin-System

## Installation

**Systemanforderungen:** PHP 8.0+, Composer, mbstring Extension

```bash
# Dependencies installieren
cd ./builder
composer install --no-dev --optimize-autoloader
```

## Verwendung

```bash
# Content bearbeiten
# - Markdown-Dateien in pages/
# - CSS-Styling in static/css/
# - Bilder in static/img/

# Website erstellen
cd builder/
php index.php
```

Die generierten HTML-Dateien befinden sich im `html/`-Verzeichnis.

## Konfiguration

Anpassungen in `builder/SiteConfig.php`:

```php
return [
    'SiteName' => 'Your Website',
    'SiteUrl' => 'yourwebsite.com',
    // ...weitere Optionen
];
```

## Lizenz

Copyright (C) 2012-2025 André Grötschel

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
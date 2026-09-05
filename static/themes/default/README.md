# Theme "default" — Tannengrün

**Charakter:** ruhiges Tannengrün auf warmem, sandfarbenem Grund. Gemäß
[ADR 0001](../../../docs/adr/0001-php-bleibt-und-theme-system.md) ist das
mitgelieferte Standard-Theme grün; blaue Paletten bleiben einzelnen
Verwender-Websites vorbehalten.

## Farbtoken

| Token | Hell | Dunkel |
|---|---|---|
| `--color-bg` | `#f6ede4` | `#1a231e` |
| `--color-bg-elevated` | `#fefefe` | `#232e28` |
| `--color-fg` | `#332f2a` | `#e2e6e0` |
| `--color-fg-muted` | `#5c564d` | `#aeb5ac` |
| `--color-primary` | `#2f6b46` | `#8fd0a8` |
| `--color-primary-hover` | `#265939` | `#b0e0c2` |
| `--color-on-primary` | `#ffffff` | `#0d1a12` |
| `--color-accent` | `#d7e8dc` | `#26382e` |
| `--color-border` | `#c0bda5` | `#3a4a41` |
| `--color-focus-ring` | `#1b4430` | `#8fd0a8` |

Jeder Wert steht zweimal: zuerst als Hex-Fallback, danach als `oklch()`.
Browser ohne `oklch()` behalten die Hex-Zeile, moderne Browser überschreiben
sie nach der Regel „letzte gültige Deklaration gewinnt".

## Gemessene Kontraste

Alle Werte nach WCAG 2.2 gemessen. Für Fließtext gilt 4,5:1, für
Bedienelemente und Fokusrahmen 3:1.

| Paarung | Hell | Dunkel |
|---|---|---|
| Fließtext auf Seitenhintergrund | 11.48:1 | 12.76:1 |
| Fließtext auf Karte | 13.17:1 | 11.14:1 |
| Gedämpfter Text auf Karte | 7.20:1 | 6.70:1 |
| Text auf Primärfläche (Navigation, Fußzeile) | 6.34:1 | 10.02:1 |
| Text auf Primärfläche im Hover | 8.17:1 | 12.17:1 |
| Überschrift auf Akzentfläche | 8.59:1 | 9.59:1 |
| Verweis auf Karte | 6.29:1 | 7.87:1 |
| Fokusrahmen auf Seitenhintergrund | 9.47:1 | 9.02:1 |

## Anpassen

Ein eigenes Theme entsteht als Kopie dieses Ordners unter einem neuen Namen
und wird über den Schlüssel `Theme` in `builder/SiteConfig.php` ausgewählt.
Der Pflichtsatz an Token muss vollständig belegt sein, sonst greifen die
Regeln in `static/css/base.css` ins Leere. Die Zeile `/* theme-api: 1.0 */`
am Dateianfang bleibt stehen — der Generator prüft daran die Fassung.

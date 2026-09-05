<?php

declare(strict_types=1);

namespace PageBuilder\Markdown\Figure;

/**
 * Einstellungen für Abbildungsblöcke.
 *
 * Die Werte beschreiben, wie aus einem kurzen Verweis im Text die vollständige
 * Auszeichnung einer Abbildung entsteht: Ablageort, verfügbare Auflösungen,
 * Layoutbreiten und ein möglicher Zusatz in der Bildunterschrift. Alles ist
 * bewusst konfigurierbar, damit die Erweiterung nicht an ein einzelnes
 * Projekt gebunden ist.
 */
final class FigureSettings
{
    /**
     * @param string            $basePath      Pfadpräfix der Bilddateien, etwa "/img/"
     * @param string            $extension     Dateiendung ohne Punkt, etwa "webp"
     * @param array<string,int> $variants      Namenszusatz => Bildbreite in Pixeln
     * @param string            $default       Namenszusatz der Fassung für das src-Attribut
     * @param string            $sizes         Wert für das sizes-Attribut
     * @param array<string,array{class:string,width:int,height:int,caption:bool}> $kinds
     *        Schlüsselwort im Text => Auszeichnung und Maße der Abbildung
     * @param string            $captionSuffix HTML, das jeder Bildunterschrift angehängt wird
     * @param string            $loading       Wert für das loading-Attribut
     */
    public function __construct(
        public readonly string $basePath = '/img/',
        public readonly string $extension = 'webp',
        public readonly array $variants = ['sml' => 600, 'mid' => 900, 'lrg' => 1200, 'xl' => 1536],
        public readonly string $default = 'lrg',
        public readonly string $sizes = '100vw',
        public readonly array $kinds = [
            'bild' => ['class' => 'illustration', 'width' => 1200, 'height' => 800, 'caption' => true],
        ],
        public readonly string $captionSuffix = '',
        public readonly string $loading = 'lazy',
    ) {
    }

    /**
     * Erzeugt die Einstellungen aus dem Konfigurationsfeld einer Website.
     *
     * @param array<string,mixed> $config
     */
    public static function fromArray(array $config): self
    {
        $standard = new self();

        return new self(
            basePath: (string) ($config['basePath'] ?? $standard->basePath),
            extension: (string) ($config['extension'] ?? $standard->extension),
            variants: (array) ($config['variants'] ?? $standard->variants),
            default: (string) ($config['default'] ?? $standard->default),
            sizes: (string) ($config['sizes'] ?? $standard->sizes),
            kinds: (array) ($config['kinds'] ?? $standard->kinds),
            captionSuffix: (string) ($config['captionSuffix'] ?? $standard->captionSuffix),
            loading: (string) ($config['loading'] ?? $standard->loading),
        );
    }

    /** Vollständiger Pfad einer Auflösungsfassung. */
    public function path(string $name, string $variant): string
    {
        return $this->basePath . $name . '-' . $variant . '.' . $this->extension;
    }

    /** Wert für das srcset-Attribut über alle konfigurierten Auflösungen. */
    public function srcset(string $name): string
    {
        $eintraege = [];
        foreach ($this->variants as $variant => $breite) {
            $eintraege[] = $this->path($name, (string) $variant) . ' ' . $breite . 'w';
        }

        return implode(', ', $eintraege);
    }

    /**
     * Auszeichnung und Maße für ein Schlüsselwort.
     *
     * @return array{class:string,width:int,height:int,caption:bool}|null
     */
    public function kind(string $keyword): ?array
    {
        return $this->kinds[$keyword] ?? null;
    }
}

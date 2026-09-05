<?php

declare(strict_types=1);

namespace PageBuilder\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalink;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;
use PageBuilder\Markdown\Figure\FigureSettings;

/**
 * Wandelt den Quelltext einer Seite in Auszeichnung um.
 *
 * Für Sprungmarken und das Inhaltsverzeichnis werden die mitgelieferten
 * CommonMark-Erweiterungen genutzt; eigener Code entsteht nur dort, wo es
 * nichts Passendes gibt: für Abbildungen und für die Entscheidung, ob und an
 * welcher Stelle ein Inhaltsverzeichnis sinnvoll ist.
 */
final class ContentRenderer
{
    private const PLATZHALTER = '[TOC]';

    private function __construct(
        private readonly MarkdownConverter $converter,
        private readonly int $tocMinLength,
        private readonly int $tocMinSections,
        private readonly string $tocLabel,
    ) {
    }

    /**
     * @param array<string,mixed> $config Konfiguration der Website
     */
    public static function create(array $config): self
    {
        $environment = new Environment([
            'heading_permalink' => [
                'id_prefix'         => '',
                'fragment_prefix'   => '',
                'symbol'            => '',
                'insert'            => 'before',
                'min_heading_level' => 2,
                'max_heading_level' => 4,
                'aria_hidden'       => true,
            ],
            'table_of_contents' => [
                'html_class'        => 'toc',
                'position'          => 'placeholder',
                'placeholder'       => self::PLATZHALTER,
                'style'             => 'ordered',
                'min_heading_level' => 2,
                'max_heading_level' => 3,
                'normalize'         => 'relative',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        // Run after CommonMark's permalink processor (-100). Empty, hidden
        // anchors remain fragment targets, but must not enter the tab order.
        $environment->addEventListener(DocumentParsedEvent::class, static function (DocumentParsedEvent $event): void {
            foreach ($event->getDocument()->iterator() as $node) {
                if ($node instanceof HeadingPermalink) {
                    $node->data->set('attributes/tabindex', '-1');
                }
            }
        }, -150);
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TableOfContentsExtension());
        $environment->addExtension(new FigureExtension(
            FigureSettings::fromArray((array) ($config['Figure'] ?? []))
        ));

        return new self(
            new MarkdownConverter($environment),
            (int) ($config['TocMinLength'] ?? 6000),
            (int) ($config['TocMinSections'] ?? 4),
            (string) ($config['TocLabel'] ?? 'Inhalt'),
        );
    }

    public function render(string $markdown): string
    {
        $html = (string) $this->converter->convert($this->addTocPlaceholder($markdown));
        $html = $this->labelTableOfContents($html);

        return $this->wrapTables($html);
    }

    /**
     * Legt um jede Tabelle einen eigenen Rahmen.
     *
     * Eine breite Tabelle soll in ihrem Rahmen scrollen und nicht die ganze
     * Seite seitlich verschieben. Das laesst sich mit CSS allein nicht loesen,
     * ohne der Tabelle ihr Layoutverhalten zu nehmen.
     */
    private function wrapTables(string $html): string
    {
        $cursor = 0;
        while (($start = \strpos($html, '<table>', $cursor)) !== false) {
            $ende = $this->findClosingTag($html, 'table', $start);
            if ($ende === null) {
                break;
            }
            // A scrolling table wrapper is not a document landmark.
            $vorne  = '<div class="tabelle" tabindex="0">';
            $hinten = '</div>';
            $html   = \substr($html, 0, $start) . $vorne
                . \substr($html, $start, $ende - $start) . $hinten
                . \substr($html, $ende);
            $cursor = $ende + \strlen($vorne) + \strlen($hinten);
        }

        return $html;
    }

    /**
     * Zerlegt den Quelltext in Blöcke und merkt sich, welche davon in einem
     * eingezäunten Codeblock liegen. In Code darf nichts eingefügt werden.
     *
     * @return list<array{text: string, code: bool}>
     */
    private function splitBlocks(string $markdown): array
    {
        $bloecke = \preg_split('/\R{2,}/u', trim($markdown));
        if ($bloecke === false) {
            return [];
        }

        $ergebnis = [];
        $imCode   = false;
        foreach ($bloecke as $block) {
            $ergebnis[] = ['text' => $block, 'code' => $imCode];
            // Ein Block kann einen Zaun öffnen und einen weiteren schließen.
            if (\preg_match_all('/^(?:```|~~~)/m', $block, $treffer) % 2 === 1) {
                $imCode = !$imCode;
            }
        }

        return $ergebnis;
    }

    /**
     * Setzt bei langen Artikeln den Platzhalter für das Inhaltsverzeichnis
     * hinter den ersten Absatz.
     *
     * Steht direkt danach eine Abbildung, rückt der Platzhalter dahinter: das
     * Einstiegsbild gehört zur Einleitung. Kurze Seiten bleiben unberührt.
     */
    private function addTocPlaceholder(string $markdown): string
    {
        if (\str_contains($markdown, self::PLATZHALTER)) {
            return $markdown;
        }
        if (\strlen($markdown) < $this->tocMinLength) {
            return $markdown;
        }
        if (\preg_match_all('/^#{2,3} \S/mu', $markdown) < $this->tocMinSections) {
            return $markdown;
        }

        $bloecke = $this->splitBlocks($markdown);
        if (\count($bloecke) < 2) {
            return $markdown;
        }

        $einfuegen = null;
        foreach ($bloecke as $index => $block) {
            $text = trim($block['text']);
            if ($block['code'] || $text === '' || \str_starts_with($text, '#')) {
                continue;
            }
            $einfuegen = $index + 1;
            break;
        }
        if ($einfuegen === null) {
            return $markdown;
        }

        // Eine Abbildung direkt nach der Einleitung gehört noch zu ihr.
        while (isset($bloecke[$einfuegen])
            && !$bloecke[$einfuegen]['code']
            && \str_starts_with(trim($bloecke[$einfuegen]['text']), '@')) {
            $einfuegen++;
        }

        $texte = \array_column($bloecke, 'text');
        \array_splice($texte, $einfuegen, 0, [self::PLATZHALTER]);

        return \implode("\n\n", $texte) . "\n";
    }

    /**
     * Fasst das erzeugte Verzeichnis in einen benannten Navigationsbereich.
     *
     * Die Erweiterung liefert nur die Liste. Ohne Beschriftung steht sie
     * unvermittelt hinter der Einleitung, und Screenreader kündigen sie nicht
     * als Inhaltsverzeichnis an.
     */
    private function labelTableOfContents(string $html): string
    {
        foreach (['ol', 'ul'] as $tag) {
            $start = \strpos($html, '<' . $tag . ' class="toc">');
            if ($start === false) {
                continue;
            }
            $ende = $this->findClosingTag($html, $tag, $start);
            if ($ende === null) {
                continue;
            }

            $titel = \htmlspecialchars($this->tocLabel, \ENT_QUOTES, 'UTF-8');
            $liste = \substr($html, $start, $ende - $start);

            return \substr($html, 0, $start)
                . '<nav class="toc-box" aria-label="' . $titel . '">'
                . '<p class="toc-box__titel">' . $titel . '</p>'
                . $liste
                . '</nav>'
                . \substr($html, $ende);
        }

        return $html;
    }

    /**
     * Sucht das schließende Gegenstück eines Elements ab einer Position.
     *
     * Bewusst ohne regulären Ausdruck: Verzeichnisse können verschachtelt
     * sein, und ein genügsames Muster würde beim ersten inneren Abschluss
     * enden. Ein Ausdruck über das gesamte Dokument könnte außerdem an der
     * Rücksetzgrenze scheitern und dann die ganze Seite leeren.
     *
     * @return int|null Position hinter dem schließenden Element
     */
    private function findClosingTag(string $html, string $tag, int $start): ?int
    {
        $offen  = '<' . $tag;
        $zu     = '</' . $tag . '>';
        $tiefe  = 0;
        $cursor = $start;

        while ($cursor < \strlen($html)) {
            $naechsteOeffnung = \strpos($html, $offen, $cursor);
            $naechsterSchluss = \strpos($html, $zu, $cursor);

            if ($naechsterSchluss === false) {
                return null;
            }
            if ($naechsteOeffnung !== false && $naechsteOeffnung < $naechsterSchluss) {
                $tiefe++;
                $cursor = $naechsteOeffnung + \strlen($offen);
                continue;
            }

            $tiefe--;
            $cursor = $naechsterSchluss + \strlen($zu);
            if ($tiefe === 0) {
                return $cursor;
            }
        }

        return null;
    }
}

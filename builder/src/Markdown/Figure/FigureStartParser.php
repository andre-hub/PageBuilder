<?php

declare(strict_types=1);

namespace PageBuilder\Markdown\Figure;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

/**
 * Erkennt eine Abbildungszeile.
 *
 * Aufbau: ein Schlüsselwort mit vorangestelltem @, danach durch senkrechte
 * Striche getrennt der Dateiname ohne Auflösungszusatz und Endung, der
 * Alternativtext und – sofern die Art eine Bildunterschrift vorsieht – deren
 * Text. Beispiel:
 *
 *     @bild wege | Karte mit mehreren Wegen | Wege durch Themen und Gedanken
 *
 * Der Dateiname ist auf Buchstaben, Ziffern, Punkt, Bindestrich und
 * Unterstrich beschränkt, damit aus ihm kein Pfadwechsel und kein Ausbruch
 * aus dem Attribut werden kann. Unbekannte Schlüsselwörter und unzulässige
 * Namen werden bewusst nicht gemeldet: Die Zeile bleibt dann ein gewöhnlicher
 * Absatz und fällt beim Korrekturlesen sofort auf.
 */
final class FigureStartParser implements BlockStartParserInterface
{
    public function __construct(private readonly FigureSettings $settings)
    {
    }

    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented() || $cursor->getNextNonSpaceCharacter() !== '@') {
            return BlockStart::none();
        }

        $zeile = trim($cursor->getRemainder());
        if (!preg_match('/^@([a-z][a-z0-9_-]*)\s+(.+)$/u', $zeile, $treffer)) {
            return BlockStart::none();
        }

        $keyword = $treffer[1];
        if ($this->settings->kind($keyword) === null) {
            return BlockStart::none();
        }

        // Höchstens drei Felder: ein senkrechter Strich in der Bildunterschrift
        // bleibt damit Teil des Textes und geht nicht verloren.
        $felder = array_map('trim', explode('|', $treffer[2], 3));
        $name   = $felder[0] ?? '';
        if ($name === '' || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $name)) {
            return BlockStart::none();
        }

        $cursor->advanceToEnd();

        return BlockStart::of(new FigureContinueParser(new Figure(
            keyword: $keyword,
            name: $name,
            alt: $felder[1] ?? '',
            caption: $felder[2] ?? '',
        )))->at($cursor);
    }
}

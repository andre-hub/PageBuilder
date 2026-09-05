<?php

declare(strict_types=1);

namespace PageBuilder\Markdown\Figure;

use League\CommonMark\Node\Block\AbstractBlock;

/**
 * Ein Abbildungsblock im Dokumentbaum.
 *
 * Der Block hält nur die im Text angegebenen Werte. Wie daraus Auszeichnung
 * wird, entscheidet allein der Renderer anhand der Einstellungen.
 */
final class Figure extends AbstractBlock
{
    public function __construct(
        public readonly string $keyword,
        public readonly string $name,
        public readonly string $alt,
        public readonly string $caption = '',
    ) {
        parent::__construct();
    }
}

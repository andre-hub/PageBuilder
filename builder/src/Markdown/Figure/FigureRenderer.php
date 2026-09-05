<?php

declare(strict_types=1);

namespace PageBuilder\Markdown\Figure;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Setzt einen Abbildungsblock in Auszeichnung um.
 *
 * Alle Angaben, die sich für jede Abbildung wiederholen – Auflösungen,
 * Layoutbreiten, Ladeverhalten und ein möglicher Zusatz in der
 * Bildunterschrift – stammen aus den Einstellungen und stehen damit an genau
 * einer Stelle.
 */
final class FigureRenderer implements NodeRendererInterface
{
    public function __construct(private readonly FigureSettings $settings)
    {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (!$node instanceof Figure) {
            throw new \InvalidArgumentException('Erwartet wurde ein Abbildungsblock.');
        }

        $art = $this->settings->kind($node->keyword);
        if ($art === null) {
            return '';
        }

        $bild = new HtmlElement('img', [
            'src'     => $this->settings->path($node->name, $this->settings->default),
            'srcset'  => $this->settings->srcset($node->name),
            'sizes'   => $this->settings->sizes,
            'loading' => $this->settings->loading,
            'width'   => (string) $art['width'],
            'height'  => (string) $art['height'],
            'alt'     => $node->alt,
        ], '', true);

        $inhalt = "\n  " . $bild->__toString();

        if ($art['caption'] && $node->caption !== '') {
            $text = \htmlspecialchars($node->caption, \ENT_QUOTES, 'UTF-8');
            if ($this->settings->captionSuffix !== '') {
                $text .= ' ' . $this->settings->captionSuffix;
            }
            $inhalt .= "\n  " . new HtmlElement('figcaption', [], $text);
        }

        return (string) new HtmlElement('figure', ['class' => $art['class']], $inhalt . "\n");
    }
}

<?php

declare(strict_types=1);

namespace PageBuilder\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use PageBuilder\Markdown\Figure\Figure;
use PageBuilder\Markdown\Figure\FigureRenderer;
use PageBuilder\Markdown\Figure\FigureSettings;
use PageBuilder\Markdown\Figure\FigureStartParser;

/**
 * Abbildungen als eigene Blocksyntax.
 *
 * Ohne diese Erweiterung steht in jedem Artikel die vollständige Auszeichnung
 * einer Abbildung mit allen Auflösungen und Layoutbreiten. Das ist fehleranfällig
 * – rohes HTML im Text setzt außerdem die Markdown-Auswertung bis zur nächsten
 * Leerzeile aus – und es verteilt eine Layoutentscheidung über sämtliche Texte.
 */
final class FigureExtension implements ExtensionInterface
{
    public function __construct(private readonly FigureSettings $settings)
    {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addBlockStartParser(new FigureStartParser($this->settings))
            ->addRenderer(Figure::class, new FigureRenderer($this->settings));
    }
}

<?php

declare(strict_types=1);

namespace PageBuilder\Markdown\Figure;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;

/**
 * Ein Abbildungsblock umfasst genau eine Zeile und wird deshalb nie
 * fortgesetzt.
 */
final class FigureContinueParser extends AbstractBlockContinueParser
{
    public function __construct(private readonly Figure $block)
    {
    }

    public function getBlock(): AbstractBlock
    {
        return $this->block;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        return BlockContinue::none();
    }
}

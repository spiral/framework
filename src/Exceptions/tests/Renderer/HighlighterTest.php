<?php

declare(strict_types=1);

namespace Spiral\Tests\Exceptions\Renderer;

use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\Renderer\Highlighter;
use Spiral\Exceptions\Style\ConsoleStyle;
use Spiral\Exceptions\Style\PlainStyle;

class HighlighterTest extends TestCase
{
    public function testPlainHighlighter(): void
    {
        $highlighter = new Highlighter(new PlainStyle());

        self::assertStringContainsString('HighlighterTest', $highlighter->highlight(\file_get_contents(__FILE__)));
    }

    public function testConsoleHighlighter(): void
    {
        $highlighter = new Highlighter(new ConsoleStyle());

        self::assertStringContainsString('HighlighterTest', $highlighter->highlight(\file_get_contents(__FILE__)));
    }

    public function testPlainHighlighterLines(): void
    {
        $highlighter = new Highlighter(new PlainStyle());

        self::assertStringContainsString('HighlighterTest', $highlighter->highlightLines(\file_get_contents(__FILE__), 17));
    }

    public function testConsoleHighlighterLines(): void
    {
        $highlighter = new Highlighter(new ConsoleStyle());

        self::assertStringContainsString('HighlighterTest', $highlighter->highlightLines(\file_get_contents(__FILE__), 17));
    }

    public function testCountLines(): void
    {
        $highlighter = new Highlighter(new PlainStyle());

        self::assertCount(1, \explode("\n", \trim($highlighter->highlightLines(\file_get_contents(__FILE__), 0, 1), "\n")));

        self::assertCount(2, \explode("\n", \trim($highlighter->highlightLines(\file_get_contents(__FILE__), 1, 1), "\n")));

        self::assertCount(3, \explode("\n", \trim($highlighter->highlightLines(\file_get_contents(__FILE__), 2, 1), "\n")));
    }
}

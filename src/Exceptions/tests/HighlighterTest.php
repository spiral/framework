<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Debug;

use PHPUnit\Framework\TestCase;
use Spiral\Exceptions\Highlighter;
use Spiral\Exceptions\Style\ConsoleStyle;
use Spiral\Exceptions\Style\HtmlStyle;
use Spiral\Exceptions\Style\PlainStyle;

class HighlighterTest extends TestCase
{
    public function testPlainHighlighter(): void
    {
        $highlighter = new Highlighter(new PlainStyle());

        $this->assertStringContainsString('HighlighterTest', $highlighter->highlight(file_get_contents(__FILE__)));
    }

    public function testHtmlHighlighter(): void
    {
        $highlighter = new Highlighter(new HtmlStyle(HtmlStyle::DEFAULT));

        $this->assertStringContainsString('HighlighterTest', $highlighter->highlight(file_get_contents(__FILE__)));
    }

    public function testInvertedHtmlHighlighter(): void
    {
        $highlighter = new Highlighter(new HtmlStyle(HtmlStyle::INVERTED));

        $this->assertStringContainsString('HighlighterTest', $highlighter->highlight(file_get_contents(__FILE__)));
    }

    public function testConsoleHighlighter(): void
    {
        $highlighter = new Highlighter(new ConsoleStyle());

        $this->assertStringContainsString('HighlighterTest', $highlighter->highlight(file_get_contents(__FILE__)));
    }

    public function testPlainHighlighterLines(): void
    {
        $highlighter = new Highlighter(new PlainStyle());

        $this->assertStringContainsString(
            'HighlighterTest',
            $highlighter->highlightLines(file_get_contents(__FILE__), 17)
        );
    }

    public function testHtmlHighlighterLines(): void
    {
        $highlighter = new Highlighter(new HtmlStyle(HtmlStyle::DEFAULT));

        $this->assertStringContainsString(
            'HighlighterTest',
            $highlighter->highlightLines(file_get_contents(__FILE__), 17)
        );
    }

    public function testInvertedHtmlHighlighterLines(): void
    {
        $highlighter = new Highlighter(new HtmlStyle(HtmlStyle::INVERTED));

        $this->assertStringContainsString(
            'HighlighterTest',
            $highlighter->highlightLines(file_get_contents(__FILE__), 17)
        );
    }

    public function testConsoleHighlighterLines(): void
    {
        $highlighter = new Highlighter(new ConsoleStyle());

        $this->assertStringContainsString(
            'HighlighterTest',
            $highlighter->highlightLines(file_get_contents(__FILE__), 17)
        );
    }

    public function testCountLines(): void
    {
        $highlighter = new Highlighter(new PlainStyle());

        $this->assertCount(
            1,
            explode("\n", trim($highlighter->highlightLines(file_get_contents(__FILE__), 0, 1), "\n"))
        );

        $this->assertCount(
            2,
            explode("\n", trim($highlighter->highlightLines(file_get_contents(__FILE__), 1, 1), "\n"))
        );

        $this->assertCount(
            3,
            explode("\n", trim($highlighter->highlightLines(file_get_contents(__FILE__), 2, 1), "\n"))
        );
    }
}

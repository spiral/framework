<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use PHPUnit\Framework\TestCase;
use Spiral\Reactor\Exception\MultilineException;
use Spiral\Reactor\Partial\Source;

class SourceTest extends TestCase
{
    public function testSource(): void
    {
        $s = Source::fromString("   \$name='antony';\r\n       return \$name;");
        $this->assertSame("   \$name='antony';", $s->getLines()[0]);
        $this->assertSame('       return $name;', $s->getLines()[1]);

        $s = Source::fromString("   \$name='antony';\r\n       return \$name;", true);
        $this->assertSame("\$name='antony';", $s->getLines()[0]);
        $this->assertSame('    return $name;', $s->getLines()[1]);
    }

    public function testNormalizeEndings(): void
    {
        $string = "line\n\rline2";
        $this->assertSame("line\nline2", Source::normalizeEndings($string));
        $string = "line\n\r\nline2";
        $this->assertSame("line\n\nline2", Source::normalizeEndings($string, false));
        $this->assertSame("line\nline2", Source::normalizeEndings($string, true));
    }

    public function testNormalizeEndingsEmptyReference(): void
    {
        $input = ['', '    b', '    c'];
        $output = ['', 'b', 'c'];
        $this->assertSame(
            implode("\n", $output),
            Source::normalizeIndents(implode("\n", $input))
        );
    }

    public function testNormalizeEndingsEmptySpaceReference(): void
    {
        $input = [' ', '    b', '    c'];
        $output = ['', 'b', 'c'];
        $this->assertSame(
            implode("\n", $output),
            Source::normalizeIndents(implode("\n", $input))
        );
    }

    public function testNormalizeEndingsNonEmptyReference(): void
    {
        $input = ['a', '    b', '    c'];
        $output = ['a', '    b', '    c'];
        $this->assertSame(
            implode("\n", $output),
            Source::normalizeIndents(implode("\n", $input))
        );
    }

    public function testAddLine(): void
    {
        $this->expectException(MultilineException::class);

        $s = new Source(['line a', 'line b']);
        $s->addLine('line c');

        $this->assertSame(['line a', 'line b', 'line c'], $s->getLines());

        $s->addLine("line d\nline e");
    }

    public function testStringify(): void
    {
        $s = new Source(['line a', 'line b']);
        $s->addLine('line c');

        $this->assertSame("line a\nline b\nline c", (string)$s);
    }
}

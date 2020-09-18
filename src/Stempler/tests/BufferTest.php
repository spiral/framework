<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use PHPUnit\Framework\TestCase;
use Spiral\Stempler\Lexer\Buffer;
use Spiral\Stempler\Lexer\Byte;
use Spiral\Stempler\Lexer\StreamInterface;
use Spiral\Stempler\Lexer\StringStream;

class BufferTest extends TestCase
{
    public function testNext(): void
    {
        $src = $this->buffer('abc');
        $this->assertEquals(new Byte(0, 'a'), $src->next());
        $this->assertEquals(new Byte(1, 'b'), $src->next());
        $this->assertEquals(new Byte(2, 'c'), $src->next());
        $this->assertEquals(null, $src->next());
    }

    public function testIterate(): void
    {
        $out = '';
        foreach ($this->buffer('abc') as $n) {
            $out .= $n->char;
        }

        $this->assertEquals('abc', $out);
    }

    public function testGetBytes(): void
    {
        $src = $this->buffer('abc');
        $this->assertEquals('abc', $src->nextBytes());

        $this->assertEquals('', $src->nextBytes());
    }

    public function testLookahead(): void
    {
        $src = $this->buffer('abc');
        $this->assertEquals('a', $src->lookahead()->char);
        $this->assertEquals(0, $src->lookahead()->offset);

        // no iteration expected
        $this->assertEquals('a', $src->lookahead()->char);
        $this->assertEquals(0, $src->lookahead()->offset);

        $this->assertEquals(new Byte(0, 'a'), $src->next());

        $this->assertEquals('b', $src->lookahead()->char);
        $this->assertEquals(1, $src->lookahead()->offset);

        // no iteration expected
        $this->assertEquals('b', $src->lookahead()->char);
        $this->assertEquals(1, $src->lookahead()->offset);

        $this->assertEquals(new Byte(1, 'b'), $src->next());

        $this->assertEquals('c', $src->lookahead()->char);
        $this->assertEquals(2, $src->lookahead()->offset);

        // no iteration expected
        $this->assertEquals('c', $src->lookahead()->char);
        $this->assertEquals(2, $src->lookahead()->offset);

        $this->assertEquals(new Byte(2, 'c'), $src->next());

        $this->assertEquals(null, $src->lookahead());
        $this->assertEquals(null, $src->lookahead());
        $this->assertEquals(null, $src->next());
    }

    public function testLookaheadByte(): void
    {
        $src = $this->buffer('abc');
        $this->assertEquals('a', $src->lookaheadByte());

        $this->assertEquals(new Byte(0, 'a'), $src->next());
        $this->assertEquals('b', $src->lookaheadByte());

        $this->assertEquals(new Byte(1, 'b'), $src->next());
        $this->assertEquals('c', $src->lookaheadByte());

        $this->assertEquals(new Byte(2, 'c'), $src->next());
        $this->assertEquals(null, $src->lookaheadByte());
    }

    public function testReplay(): void
    {
        $src = $this->buffer('abc');
        $this->assertEquals(new Byte(0, 'a'), $a = $src->next());
        $this->assertEquals(new Byte(1, 'b'), $b = $src->next());
        $this->assertEquals(new Byte(2, 'c'), $c = $src->next());

        $src->replay($a->offset);
        $this->assertEquals(new Byte(1, 'b'), $src->next());
        $this->assertEquals(new Byte(2, 'c'), $src->next());

        $src->replay($b->offset);
        $this->assertEquals(new Byte(2, 'c'), $src->next());

        $src->replay($c->offset);
        $this->assertEquals(null, $src->next());
    }

    public function testOffset(): void
    {
        $src = $this->buffer('abc');
        $this->assertEquals(0, $src->getOffset());

        $this->assertEquals(new Byte(0, 'a'), $src->next());
        $this->assertEquals(0, $src->getOffset());

        $this->assertEquals(new Byte(1, 'b'), $src->next());
        $this->assertEquals(1, $src->getOffset());

        $this->assertEquals(new Byte(2, 'c'), $src->next());
        $this->assertEquals(2, $src->getOffset());
    }

    public function testLookupBytes(): void
    {
        $src = $this->buffer('abc');
        $this->assertEquals(0, $src->getOffset());

        $this->assertEquals('ab', $src->lookaheadByte(2));

        $this->assertEquals(new Byte(0, 'a'), $src->next());

        $this->assertEquals('bc', $src->lookaheadByte(2));
        $this->assertEquals('bc', $src->lookaheadByte(3));

        $this->assertEquals(new Byte(1, 'b'), $src->next());
    }

    protected function buffer(string $string)
    {
        return new Buffer($this->generate(new StringStream($string)));
    }

    private function generate(StreamInterface $src)
    {
        while (!$src->isEOI()) {
            yield new Byte($src->getOffset(), $src->peak());
        }
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use PHPUnit\Framework\TestCase;
use Spiral\Stempler\Lexer\Buffer;
use Spiral\Stempler\Lexer\Byte;
use Spiral\Stempler\Lexer\StreamInterface;
use Spiral\Stempler\Lexer\StringStream;
use Spiral\Stempler\Lexer\Token;

class BufferTest extends TestCase
{
    public function testNext(): void
    {
        $src = $this->buffer('abc');
        self::assertEquals(new Byte(0, 'a'), $src->next());
        self::assertEquals(new Byte(1, 'b'), $src->next());
        self::assertEquals(new Byte(2, 'c'), $src->next());
        self::assertEquals(null, $src->next());
    }

    public function testIterate(): void
    {
        $out = '';
        foreach ($this->buffer('abc') as $n) {
            $out .= $n->char;
        }

        self::assertSame('abc', $out);
    }

    public function testGetBytes(): void
    {
        $src = $this->buffer('abc');
        self::assertEquals('abc', $src->nextBytes());

        self::assertEquals('', $src->nextBytes());
    }

    public function testLookahead(): void
    {
        $src = $this->buffer('abc');
        self::assertEquals('a', $src->lookahead()->char);
        self::assertEquals(0, $src->lookahead()->offset);

        // no iteration expected
        self::assertEquals('a', $src->lookahead()->char);
        self::assertEquals(0, $src->lookahead()->offset);

        self::assertEquals(new Byte(0, 'a'), $src->next());

        self::assertEquals('b', $src->lookahead()->char);
        self::assertEquals(1, $src->lookahead()->offset);

        // no iteration expected
        self::assertEquals('b', $src->lookahead()->char);
        self::assertEquals(1, $src->lookahead()->offset);

        self::assertEquals(new Byte(1, 'b'), $src->next());

        self::assertEquals('c', $src->lookahead()->char);
        self::assertEquals(2, $src->lookahead()->offset);

        // no iteration expected
        self::assertEquals('c', $src->lookahead()->char);
        self::assertEquals(2, $src->lookahead()->offset);

        self::assertEquals(new Byte(2, 'c'), $src->next());

        self::assertEquals(null, $src->lookahead());
        self::assertEquals(null, $src->lookahead());
        self::assertEquals(null, $src->next());
    }

    public function testLookaheadByte(): void
    {
        $src = $this->buffer('abc');
        self::assertEquals('a', $src->lookaheadByte());

        self::assertEquals(new Byte(0, 'a'), $src->next());
        self::assertEquals('b', $src->lookaheadByte());

        self::assertEquals(new Byte(1, 'b'), $src->next());
        self::assertEquals('c', $src->lookaheadByte());

        self::assertEquals(new Byte(2, 'c'), $src->next());
        self::assertEquals(null, $src->lookaheadByte());
    }

    public function testReplay(): void
    {
        $src = $this->buffer('abc');
        self::assertEquals(new Byte(0, 'a'), $a = $src->next());
        self::assertEquals(new Byte(1, 'b'), $b = $src->next());
        self::assertEquals(new Byte(2, 'c'), $c = $src->next());

        $src->replay($a->offset);
        self::assertEquals(new Byte(1, 'b'), $src->next());
        self::assertEquals(new Byte(2, 'c'), $src->next());

        $src->replay($b->offset);
        self::assertEquals(new Byte(2, 'c'), $src->next());

        $src->replay($c->offset);
        self::assertEquals(null, $src->next());
    }

    public function testOffset(): void
    {
        $src = $this->buffer('abc');
        self::assertEquals(0, $src->getOffset());

        self::assertEquals(new Byte(0, 'a'), $src->next());
        self::assertEquals(0, $src->getOffset());

        self::assertEquals(new Byte(1, 'b'), $src->next());
        self::assertEquals(1, $src->getOffset());

        self::assertEquals(new Byte(2, 'c'), $src->next());
        self::assertEquals(2, $src->getOffset());

        $src = new Buffer($this->generateToken(new StringStream('abc')));
        self::assertEquals(new Token(0, null, 'a'), $src->next());
        self::assertSame(0, $src->getOffset());
    }

    public function testLookupBytes(): void
    {
        $src = $this->buffer('abc');
        self::assertEquals(0, $src->getOffset());

        self::assertEquals('ab', $src->lookaheadByte(2));

        self::assertEquals(new Byte(0, 'a'), $src->next());

        self::assertEquals('bc', $src->lookaheadByte(2));
        self::assertEquals('bc', $src->lookaheadByte(3));

        self::assertEquals(new Byte(1, 'b'), $src->next());
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

    private function generateToken(StreamInterface $src): \Generator
    {
        while (!$src->isEOI()) {
            yield new Token(0, null, $src->peak());
        }
    }
}

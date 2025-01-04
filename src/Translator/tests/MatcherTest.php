<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Translator\Matcher;

class MatcherTest extends TestCase
{
    public function testIsPattern(): void
    {
        $patternizer = new Matcher();
        self::assertFalse($patternizer->isPattern('abc'));
        self::assertTrue($patternizer->isPattern('ab*'));
        self::assertTrue($patternizer->isPattern('ab(d|e)'));
    }

    #[DataProvider('patternProvider')]
    public function testMatch(string $string, string $pattern, bool $result): void
    {
        $matcher = new Matcher();
        self::assertSame($result, $matcher->matches($string, $pattern));
    }

    public static function patternProvider(): \Traversable
    {
        yield ['string', 'string', true];
        yield ['string', 'st*', true];
        yield ['abc', 'dce', false];
        yield ['abc', 'a(bc|de)', true];
        yield ['ade', 'a(bc|de)', true];
        yield ['string', '*ring', true];
        yield ['ring', '*ring', false];
        yield ['strings', '*ri(ng|ngs)', true];
    }
}

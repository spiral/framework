<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Translator\Matcher;

class PatternizerTest extends TestCase
{
    public function testIsPattern(): void
    {
        $patternizer = new Matcher();
        $this->assertFalse($patternizer->isPattern('abc'));
        $this->assertTrue($patternizer->isPattern('ab*'));
        $this->assertTrue($patternizer->isPattern('ab(d|e)'));
    }

    /**
     * @dataProvider patternProvider
     *
     * @param array $string
     * @param array $pattern
     * @param bool  $result
     */
    public function testMatch($string, $pattern, $result): void
    {
        $matcher = new Matcher();
        $this->assertEquals($result, $matcher->matches($string, $pattern));
    }

    /**
     * @return array
     */
    public function patternProvider()
    {
        return [
            ['string', 'string', true],
            ['string', 'st*', true],
            ['abc', 'dce', false],
            ['abc', 'a(bc|de)', true],
            ['ade', 'a(bc|de)', true],
            ['string', '*ring', true],
            ['ring', '*ring', false],
            ['strings', '*ri(ng|ngs)', true],
        ];
    }
}

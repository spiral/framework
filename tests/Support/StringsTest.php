<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Support;

use Cocur\Slugify\SlugifyInterface;
use Spiral\Support\Strings;
use Spiral\Tests\BaseTest;

class StringsTest extends BaseTest
{
    public function testRandom()
    {
        $this->assertSame(32, strlen(Strings::random(32)));
        $this->assertSame(64, strlen(Strings::random(64)));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testRandomBad()
    {
        Strings::random(0);
    }

    public function testSlug()
    {
        /**
         * @var SlugifyInterface $slugify
         */
        $slugify = $this->app->container->get(SlugifyInterface::class);

        $this->assertSame(
            $slugify->slugify('test'),
            Strings::slug('test')
        );

        $this->assertSame(
            $slugify->slugify('hello world'),
            Strings::slug('hello world')
        );

        $this->assertSame(
            $slugify->slugify('#what*wrong', '_'),
            Strings::slug('#what*wrong', '_')
        );
    }

    public function testEscape()
    {
        $this->assertSame('', Strings::escape('', true));
        $this->assertSame('', Strings::escape($this->app, true));

        $this->assertSame('', Strings::escape('', false));
        $this->assertSame('', Strings::escape($this->app, false));

        $this->assertSame('hello', Strings::escape('<b>hello</b>', true));
        $this->assertSame(
            '&lt;b&gt;hello&lt;/b&gt;',
            Strings::escape('<b>hello</b>', false)
        );
    }

    public function testShorterButSmaller()
    {
        $this->assertSame('abc', Strings::shorter('abc', 300));
    }

    public function testShorterButLonger()
    {
        $this->assertSame('long...', Strings::shorter('long content', 7));
    }

    public function testShorterButLongerUTF8()
    {
        $this->assertSame('привет...', Strings::shorter('привет мир', 9));
    }

    public function testBytes()
    {
        $this->assertSame('100 B', Strings::bytes(100));
        $this->assertSame('1,024 B', Strings::bytes(1024));
        $this->assertSame('1.0 kB', Strings::bytes(1025));
        $this->assertSame('100.0 kB', Strings::bytes(1024 * 100));
        $this->assertSame('100.0 MB', Strings::bytes(1024 * 1024 * 100));
        $this->assertSame('100.0 GB', Strings::bytes(1024 * 1024 * 1024 * 100));
    }

    public function normalizeEndings()
    {
        $string = "line\n\rline2";
        $this->assertSame("line\nline2", Strings::normalizeEndings($string));

        $string = "line\n\r\nline2";
        $this->assertSame("line\n\nline2", Strings::normalizeEndings($string, false));
        $this->assertSame("line\nline2", Strings::normalizeEndings($string, true));
    }
}
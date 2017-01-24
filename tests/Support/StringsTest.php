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
}
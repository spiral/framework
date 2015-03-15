<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Helpers;

use Spiral\Helpers\StringHelper;
use Spiral\Support\Tests\TestCase;

class StringHelperTest extends TestCase
{
    public function testRandom()
    {
        $this->assertEquals(32, strlen(StringHelper::random(32)));
        $this->assertEquals(16, strlen(StringHelper::random(16)));
        $this->assertEquals(8, strlen(StringHelper::random(8)));
        $this->assertEquals(3, strlen(StringHelper::random(3)));
        $this->assertEquals(7, strlen(StringHelper::random(7)));
    }

    public function testEscape()
    {
        $this->assertSame(
            'link body',
            StringHelper::escape('<a href="">link body</a>', true)
        );

        $this->assertSame(
            'link &quot;body&quot;',
            StringHelper::escape('<a href=""><b>link</b> "body"</a>', true)
        );

        $this->assertSame(
            '&lt;a href=&quot;&quot;&gt;link body&quot;&lt;/a&gt;',
            StringHelper::escape('<a href="">link body"</a>', false)
        );
    }

    public function testURL()
    {
        $this->assertSame('Test-url-message', StringHelper::url('Test url+message!'));
        $this->assertSame('Be-good-very-good', StringHelper::url('Be good, very good!'));
    }

    public function testShorter()
    {
        $this->assertLessThanOrEqual(10, strlen(StringHelper::shorter('Long sentence...', 10)));
    }

    public function testNormalization()
    {
        $this->assertSame("TEXT\nABC", StringHelper::normalizeEndings("TEXT\n\rABC"));

        $this->assertSame(
            StringHelper::normalizeEndings("TEXT\n\nABC"),
            StringHelper::normalizeEndings("TEXT\n\rABC")
        );
    }

    public function testFormatBytes()
    {
        $this->assertSame('1,024 B', StringHelper::formatBytes(1024));
        $this->assertSame('1.0 kB', StringHelper::formatBytes(1025));
        $this->assertSame('10.0 kB', StringHelper::formatBytes(1024 * 10));
        $this->assertSame('10.0 MB', StringHelper::formatBytes(1024 * 1024 * 10));
        $this->assertSame('10.1 MB', StringHelper::formatBytes(1024 * 1024 * 10 + 1024 * 100));
    }

    public function testInterpolate()
    {
        //String formatting
        $this->assertSame(
            'Brown fox.',
            StringHelper::interpolate('Brown {fox}.', array('fox' => 'fox')));

        $this->assertNotSame(
            'Brown fox.',
            StringHelper::interpolate('Brown {fox}.', array('fox' => 'dog'))
        );
    }

    public function testNormalizeIndents()
    {
        $string = <<<EOT
    a
        b
            c
            d
            e
EOT;

        $expected = <<<EOT
a
    b
        c
        d
        e
EOT;

        $this->assertSame(
            StringHelper::normalizeEndings($expected),
            StringHelper::normalizeIndents($string)
        );

        $string = <<<EOT
        a
            b
                c
                d
                e
EOT;

        $this->assertSame(
            StringHelper::normalizeEndings($expected),
            StringHelper::normalizeIndents($string)
        );
    }
}
<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests\Cases\Helpers;

use Spiral\Helpers\UrlHelper;
use Spiral\Support\Tests\TestCase;

class UrlHelperTest extends TestCase
{
    public function testNormalize()
    {
        $this->assertSame('http://google.com', UrlHelper::normalizeURL('google.com'));

        $this->assertSame('http://google.com', UrlHelper::normalizeURL('http://google.com'));
        $this->assertSame('https://google.com', UrlHelper::normalizeURL('https://google.com'));
    }

    public function testConvert()
    {
        $this->assertSame('Test-url-message', UrlHelper::slug('Test url+message!'));
        $this->assertSame('Be-good-very-good', UrlHelper::slug('Be good, very good!'));
    }
}
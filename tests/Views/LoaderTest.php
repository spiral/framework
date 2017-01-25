<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;

class LoaderTest extends BaseTest
{
    public function testFetch()
    {
        $loader = $this->views->getLoader();

        $this->assertSame('native.php', $loader->fetchName('@default/native.php'));
        $this->assertSame('default', $loader->fetchNamespace('@default/native.php'));
    }

    /**
     * @expectedException \Spiral\Views\Exceptions\LoaderException
     */
    public function testFetchInvalid()
    {
        $loader = $this->views->getLoader();

        $this->assertSame('native.php', $loader->fetchName('@default/
        native.php'));
    }

    public function testImmutable()
    {
        $loader = $this->views->getLoader();
        $nativeLoader = $loader->withExtension('php');

        $this->assertNotSame($loader, $nativeLoader);

        $this->assertSame('native', $nativeLoader->fetchName('@default/native'));
        $this->assertSame('default', $nativeLoader->fetchNamespace('@default/native'));
    }
}
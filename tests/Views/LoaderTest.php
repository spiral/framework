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
    public function testNamespaces()
    {
        $loader = $this->views->getLoader();

        $this->assertArrayHasKey('default', $loader->getNamespaces());
        $this->assertArrayHasKey('spiral', $loader->getNamespaces());
    }

    public function testFetch()
    {
        $loader = $this->views->getLoader();

        $context = $loader->getSourceContext('@default/native.php');

        $this->assertSame('native.php', $context->getName());
        $this->assertSame('default', $context->getNamespace());
    }

    public function testFetchIsolated()
    {
        $loader = $this->views->getLoader()->withExtension('php');

        $context = $loader->getSourceContext('@default/native.php');

        $this->assertSame('native', $context->getName());
        $this->assertSame('default', $context->getNamespace());
    }

    /**
     * @expectedException \Spiral\Views\Exceptions\LoaderException
     */
    public function testFetchInvalid()
    {
        $loader = $this->views->getLoader();

        $this->assertSame('native.php', $loader->getSourceContext('@default/
        native.php'));
    }

    /**
     * @expectedException \Spiral\Views\Exceptions\LoaderException
     */
    public function testFetchInvalid2()
    {
        $loader = $this->views->getLoader();

        $this->assertSame('native.php',
            $loader->getSourceContext('@default~native.php')->getName());
    }

    /**
     * @expectedException \Spiral\Views\Exceptions\LoaderException
     */
    public function testFetchInvalidNamespace()
    {
        $loader = $this->views->getLoader();

        $this->assertSame('native.php', $loader->getSourceContext('@magic/native.php')->getName());
    }

    public function testImmutable()
    {
        $loader = $this->views->getLoader();
        $nativeLoader = $loader->withExtension('php');

        $this->assertNotSame($loader, $nativeLoader);

        $this->assertSame('native', $nativeLoader->getSourceContext('@default/native')->getName());
        $this->assertSame(
            'default',
            $nativeLoader->getSourceContext('@default/native')->getNamespace()
        );
    }
}
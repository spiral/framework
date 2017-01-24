<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;

class NativeEngineTest extends BaseTest
{
    public function testRenderSimple()
    {
        $this->assertSame('Hello, World!', $this->views->render('native', [
            'name' => 'World'
        ]));
    }

    public function testRenderSimpleWithExtension()
    {
        $this->assertSame('Hello, World!', $this->views->render('native.php', [
            'name' => 'World'
        ]));
    }

    public function testRenderNamespaced()
    {
        $this->assertSame('Hello, World!', $this->views->render('default:native', [
            'name' => 'World'
        ]));
    }

    public function testRenderNamespacedWithExtension()
    {
        $this->assertSame('Hello, World!', $this->views->render('default:native.php', [
            'name' => 'World'
        ]));
    }

    /**
     * @expectedException \Spiral\Views\Exceptions\RenderException
     */
    public function testRenderException()
    {
        $this->views->render('native');
    }
}
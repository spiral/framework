<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;
use Spiral\Views\Exceptions\RenderException;

class NativeTest extends BaseTest
{
    public function testRenderSimple()
    {
        $this->assertSame('Hello, World!', $this->views->render('native', [
            'name' => 'World'
        ]));
    }

    public function testBuffer()
    {
        ob_start();
        ob_start();

        $level = ob_get_level();

        $this->assertSame('Hello, World!', $this->views->render('native', [
            'name' => 'World'
        ]));

        $this->assertSame($level, ob_get_level());
        ob_end_clean();
        ob_end_clean();
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

    public function testRenderNamespacedAlternative()
    {
        $this->assertSame('Hello, World!', $this->views->render('@default/native', [
            'name' => 'World'
        ]));
    }

    public function testRenderNamespacedWithExtension()
    {
        $this->assertSame('Hello, World!', $this->views->render('default:native.php', [
            'name' => 'World'
        ]));
    }

    public function testRenderNamespacedWithExtensionAlternative()
    {
        $this->assertSame('Hello, World!', $this->views->render('@default/native.php', [
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

    public function testBufferWhenException()
    {
        ob_start();
        ob_start();

        $level = ob_get_level();
        try {
            $this->views->render('native');
        } catch (RenderException $e) {

        }

        $this->assertSame($level, ob_get_level());
        ob_end_clean();
        ob_end_clean();
    }
}
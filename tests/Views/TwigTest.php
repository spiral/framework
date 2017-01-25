<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;

class TwigTest extends BaseTest
{
    public function testRenderSimple()
    {
        $this->assertContains(
            'Welcome to Spiral Framework',
            $this->views->render('hello')
        );
    }

    public function testRenderSimpleWithExtension()
    {
        $this->assertContains(
            'Welcome to Spiral Framework',
            $this->views->render('hello.twig')
        );
    }

    public function testRenderNamespaced()
    {
        $this->assertContains(
            'Welcome to Spiral Framework',
            $this->views->render('default:hello')
        );
    }

    public function testRenderNamespacedAlternative()
    {
        $this->assertContains(
            'Welcome to Spiral Framework',
            $this->views->render('@default/hello')
        );
    }

    public function testRenderNamespacedWithExtension()
    {
        $this->assertContains(
            'Welcome to Spiral Framework',
            $this->views->render('default:hello.twig')
        );
    }

    public function testRenderNamespacedAlternativeWithExtension()
    {
        $this->assertContains(
            'Welcome to Spiral Framework',
            $this->views->render('@default/hello.twig')
        );
    }

    public function testSpiralExtension()
    {
        $this->assertContains('Timezone: UTC', $this->views->render('hello'));
        $this->app->setTimezone('Europe/Minsk');
        $this->assertContains('Timezone: Europe/Minsk', $this->views->render('hello'));
    }

    /**
     * @expectedException \Spiral\Views\Engines\Twig\Exceptions\SyntaxException
     */
    public function testSyntaxException()
    {
        $this->views->render('invalid.twig');
    }
}
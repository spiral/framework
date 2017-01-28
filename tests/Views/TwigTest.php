<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;
use Spiral\Views\Engines\Twig\TwigView;
use Spiral\Views\Loaders\FileLoader;

class TwigTest extends BaseTest
{
    public function testTwigAccess()
    {
        $this->assertInstanceOf(
            \Twig_Environment::class,
            $this->views->engine('twig')->getTwig()
        );
    }

    public function testCompile()
    {
        $this->views->compile('isolated-y');

        clearstatcache();
        $this->assertNotEmpty($this->files->getFiles(
            $this->views->getEnvironment()->cacheDirectory()
        ));
    }

    public function testCompileWithEnvironment()
    {
        $this->views->compile('isolated-x');

        $result = $this->views->withEnvironment(
            $this->views->getEnvironment()->withDependency('value', function () {
                return 'test';
            })
        )->render('valued');

        $this->assertSame('test', $result);
    }

    public function testRenderFromOtherLoader()
    {
        $this->assertSame('Hello, World!', $this->views->render('native', [
            'name' => 'World'
        ]));

        $views = $this->views->withLoader(
            new FileLoader(
                ['default' => [directory('application') . 'alternative/']],
                $this->files
            )
        );

        $this->assertSame('cba', $views->render('isolated'));
    }

    public function testView()
    {
        $this->assertInstanceOf(TwigView::class, $this->views->get('hello'));
    }

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
     * @expectedException \Spiral\Views\Engines\Twig\Exceptions\CompileException
     */
    public function testSyntaxException()
    {
        $this->views->render('invalid.twig');
    }
}
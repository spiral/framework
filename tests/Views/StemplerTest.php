<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;
use Spiral\Views\Engines\Stempler\StemplerCache;
use Spiral\Views\Engines\Stempler\StemplerView;
use Spiral\Views\ViewLoader;

class StemplerTest extends BaseTest
{
    public function testRenderSimple()
    {
        $this->assertSame('Hello, World!', trim($this->views->render('home', [
            'name' => 'World'
        ])));
    }

    public function testCompile()
    {
        clearstatcache();
        $this->assertEmpty($this->files->getFiles(
            $this->views->getEnvironment()->cacheDirectory()
        ));
        $this->views->compile('home');

        clearstatcache();
        $this->assertNotEmpty($this->files->getFiles(
            $this->views->getEnvironment()->cacheDirectory()
        ));
    }

    public function testCompileWithEnvironment()
    {
        $this->views->compile('home');

        $this->views->withEnvironment(
            $this->views->getEnvironment()->withDependency('value', function () {
                return 'test';
            })
        )->compile('home');

        clearstatcache();
        $this->assertNotEmpty($this->files->getFiles(
            $this->views->getEnvironment()->cacheDirectory()
        ));
    }

    public function testRenderFromOtherLoader()
    {
        $this->assertSame('Hello, World!', $this->views->render('native', [
            'name' => 'World'
        ]));

        $views = $this->views->withLoader(
            new ViewLoader(
                ['default' => [directory('application') . 'alternative/']],
                $this->files
            )
        );

        $this->assertSame('home alt', $views->render('home'));
    }

    public function testRenderFromCache()
    {
        clearstatcache();
        $this->assertEmpty($this->files->getFiles(
            $this->views->getEnvironment()->cacheDirectory()
        ));

        $this->views->compile('home');

        $cache = new StemplerCache(
            $this->views->getEnvironment(),
            $this->files
        );

        $source = $this->views->getLoader()->getSource('home');

        $cache->write($cache->cacheFilename($source), 'abc');

        $this->assertSame('abc', $this->views->render('home'));
    }

    public function testView()
    {
        $this->assertInstanceOf(StemplerView::class, $this->views->get('home'));
    }
}
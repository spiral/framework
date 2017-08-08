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
use Spiral\Views\ViewCacheLocator;
use Spiral\Views\ViewLoader;

class StemplerTest extends BaseTest
{
    protected function deleteCacheFiles()
    {
        foreach ($this->files->getFiles($this->views->getEnvironment()->cacheDirectory()) as $filename) {
            //If exception is thrown here this will mean that application wasn't correctly
            //destructed and there is open resources kept
            $this->files->delete($filename);
        }
    }

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
        $this->deleteCacheFiles();
        clearstatcache();

        $this->assertContains('Hello, World!', $this->views->render('home', [
            'name' => 'World'
        ]));

        $views = $this->views->withLoader(
            new ViewLoader(
                ['default' => [directory('application') . 'alternative/']],
                $this->files
            )
        );

        $this->assertContains('home alt', $views->render('home'));
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

        $source = $this->views->getLoader()->withExtension('dark.php')->getSource('home');

        $cache->write($cache->cacheFilename($source), 'abc');

        $this->assertSame('abc', $this->views->render('home'));
    }

    public function testView()
    {
        $this->assertInstanceOf(StemplerView::class, $this->views->get('home'));
    }

    public function testCacheLocator()
    {
        $this->deleteCacheFiles();
        clearstatcache();

        $this->views->withEnvironment(
            $this->views->getEnvironment()->withDependency('value', function () {
                return 'test-one';
            })
        )->compile('home');

        $this->views->withEnvironment(
            $this->views->getEnvironment()->withDependency('value', function () {
                return 'test-two';
            })
        )->compile('home');

        /** @var ViewCacheLocator $data */
        $viewCacheLocator = $this->container->get(ViewCacheLocator::class);

        $this->assertSame(2, count($viewCacheLocator->getFiles('home')));
    }
}
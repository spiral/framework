<?php

declare(strict_types=1);

namespace Spiral\Tests\Views;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Views\Config\ViewsConfig;
use Spiral\Views\Context\ValueDependency;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\Exception\ViewException;
use Spiral\Views\GlobalVariables;
use Spiral\Views\LoaderInterface;
use Spiral\Views\ViewCache;
use Spiral\Views\ViewLoader;
use Spiral\Views\ViewManager;

class ManagerTest extends TestCase
{
    protected $container;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->bindSingleton(LoaderInterface::class, ViewLoader::class);
    }

    public function testMultipleEngines(): void
    {
        $manager = $this->makeManager();

        $manager->addEngine(new NativeEngine($this->container, 'dark.php'));

        $ext = [];
        foreach ($manager->getEngines() as $e) {
            $ext[] = $e->getLoader()->getExtension();
        }

        $this->assertSame(['dark.php', 'php'], $ext);
    }

    public function testRender(): void
    {
        $manager = $this->makeManager();
        $manager->addDependency(new ValueDependency('name', 'hello'));

        $this->assertSame('hello', $manager->render('other:var', ['value' => 'hello']));
    }

    public function testGet(): void
    {
        $manager = $this->makeManager();
        $manager->addDependency(new ValueDependency('name', 'hello'));

        $view = $manager->get('other:var');
        $this->assertSame($view, $manager->get('other:var'));

        $manager->reset('other:var');
        $this->assertNotSame($view, $manager->get('other:var'));
    }

    public function testCompile(): void
    {
        $manager = $this->makeManager();

        $r = new \ReflectionObject($manager);
        $p = $r->getProperty('cache');
        $p->setAccessible(true);
        /** @var ViewCache $cache */
        $cache = $p->getValue($manager);

        $manager->addDependency(new ValueDependency('name', 'hello'));
        $manager->render('other:var', ['value' => 'hello']);
        $this->assertTrue($cache->has($manager->getContext(), 'other:var'));

        $manager->compile('other:var');
        $this->assertFalse($cache->has($manager->getContext(), 'other:var'));
    }

    public function testReset(): void
    {
        $manager = $this->makeManager();

        $r = new \ReflectionObject($manager);
        $p = $r->getProperty('cache');
        $p->setAccessible(true);
        /** @var ViewCache $cache */
        $cache = $p->getValue($manager);

        $manager->addDependency(new ValueDependency('name', 'hello'));
        $manager->render('other:var', ['value' => 'hello']);

        $this->assertTrue($cache->has($manager->getContext(), 'other:var'));
        $manager->reset('other:var');

        $this->assertFalse($cache->has($manager->getContext(), 'other:var'));
    }

    public function testEngines(): void
    {
        $manager = $this->makeManager();
        $this->assertInstanceOf(NativeEngine::class, $manager->getEngines()[0]);
    }

    public function testNotFound(): void
    {
        $this->expectException(ViewException::class);

        $manager = $this->makeManager();
        $manager->render('hell-world');
    }

    protected function makeManager(array $config = []): ViewManager
    {
        return new ViewManager(
            new ViewsConfig(
                [
                    'cache' => [
                        'enable' => true,
                        'memory' => true,
                        'directory' => '/tmp',
                    ],
                    'namespaces' => [
                        'default' => __DIR__.'/fixtures/default',
                        'other' => __DIR__.'/fixtures/other',
                    ],
                    'dependencies' => [

                    ],
                    'engines' => [
                        NativeEngine::class,
                    ],
                ] + $config
            ),
            new GlobalVariables(),
            $this->container
        );
    }
}

<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Views;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Views\Config\ViewsConfig;
use Spiral\Views\Context\ValueDependency;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\Exception\ConfigException;

class ConfigTest extends TestCase
{
    public function testCache(): void
    {
        $config = new ViewsConfig([
            'cache' => [
                'enable'    => true,
                'directory' => '/tmp',
            ],
        ]);

        $this->assertTrue($config->isCacheEnabled());
        $this->assertSame('/tmp/', $config->getCacheDirectory());
    }

    public function testNamespace(): void
    {
        $config = new ViewsConfig([
            'namespaces' => [
                'default' => [__DIR__],
            ],
        ]);

        $this->assertSame([
            'default' => [__DIR__],
        ], $config->getNamespaces());
    }

    public function testEngines(): void
    {
        $container = new Container();

        $config = new ViewsConfig([
            'engines' => [new Autowire(NativeEngine::class)],
        ]);

        $this->assertInstanceOf(
            NativeEngine::class,
            $config->getEngines()[0]->resolve($container)
        );

        $config = new ViewsConfig([
            'engines' => [NativeEngine::class],
        ]);

        $this->assertInstanceOf(
            NativeEngine::class,
            $config->getEngines()[0]->resolve($container)
        );
    }

    public function testDependencies(): void
    {
        $container = new Container();
        $container->bindSingleton(
            'localeDependency',
            $dependency = new ValueDependency('locale', 'en', ['en', 'ru'])
        );

        $config = new ViewsConfig([
            'dependencies' => [
                'localeDependency',
            ],
        ]);

        $this->assertSame(
            $dependency,
            $config->getDependencies()[0]->resolve($container)
        );
    }

    public function testDependenciesError(): void
    {
        $this->expectException(ConfigException::class);

        $container = new Container();

        $config = new ViewsConfig([
            'dependencies' => [
                $this,
            ],
        ]);

        $config->getDependencies();
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Loader;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Router\Loader\LoaderRegistry;
use Spiral\Router\Loader\PhpFileLoader;
use Spiral\Tests\Router\Stub\TestLoader;

final class LoaderRegistryTest extends TestCase
{
    public function testResolve(): void
    {
        $container = new Container();
        $registry = new LoaderRegistry([
            new PhpFileLoader($container, $container),
            new TestLoader()
        ]);

        $this->assertInstanceOf(PhpFileLoader::class, $registry->resolve('test/file.php'));
        $this->assertInstanceOf(PhpFileLoader::class, $registry->resolve('test/file.php', 'php'));
        $this->assertFalse($registry->resolve('test/file.php', 'txt'));

        $this->assertInstanceOf(TestLoader::class, $registry->resolve('test/file.yaml', 'yaml'));
        $this->assertFalse($registry->resolve('test/file.yaml', 'php'));
    }

    public function testAddAndGetLoaders(): void
    {
        $registry = new LoaderRegistry();
        $container = new Container();
        $php = new PhpFileLoader($container, $container);
        $yaml = new TestLoader();

        $this->assertSame([], $registry->getLoaders());

        $registry->addLoader($php);
        $this->assertSame([$php], $registry->getLoaders());

        $registry->addLoader($yaml);
        $this->assertSame([$php, $yaml], $registry->getLoaders());
    }
}

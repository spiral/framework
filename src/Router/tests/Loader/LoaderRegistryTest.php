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
            new TestLoader(),
        ]);

        self::assertInstanceOf(PhpFileLoader::class, $registry->resolve('test/file.php'));
        self::assertInstanceOf(PhpFileLoader::class, $registry->resolve('test/file.php', 'php'));
        self::assertFalse($registry->resolve('test/file.php', 'txt'));

        self::assertInstanceOf(TestLoader::class, $registry->resolve('test/file.yaml', 'yaml'));
        self::assertFalse($registry->resolve('test/file.yaml', 'php'));
    }

    public function testAddAndGetLoaders(): void
    {
        $registry = new LoaderRegistry();
        $container = new Container();
        $php = new PhpFileLoader($container, $container);
        $yaml = new TestLoader();

        self::assertSame([], $registry->getLoaders());

        $registry->addLoader($php);
        self::assertSame([$php], $registry->getLoaders());

        $registry->addLoader($yaml);
        self::assertSame([$php, $yaml], $registry->getLoaders());
    }
}

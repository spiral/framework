<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Loader;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Router\Loader\DelegatingLoader;
use Spiral\Router\Loader\LoaderRegistry;
use Spiral\Router\Loader\PhpFileLoader;
use Spiral\Router\RouteCollection;
use Spiral\Tests\Router\Stub\TestLoader;

final class DelegatingLoaderTest extends TestCase
{
    public function testLoad(): void
    {
        $loader = new DelegatingLoader(new LoaderRegistry([
            new TestLoader(),
        ]));

        self::assertInstanceOf(RouteCollection::class, $loader->load('file.yaml', 'yaml'));
    }

    public function testSupports(): void
    {
        $loader = new DelegatingLoader(new LoaderRegistry());

        self::assertFalse($loader->supports('file.php'));
        self::assertFalse($loader->supports('file.php', 'php'));

        $container = new Container();

        $loader = new DelegatingLoader(new LoaderRegistry([new PhpFileLoader($container, $container)]));
        self::assertTrue($loader->supports('file.php'));
        self::assertTrue($loader->supports('file.php', 'php'));
        self::assertFalse($loader->supports('file.php', 'yaml'));
        self::assertFalse($loader->supports('file.yaml'));
        self::assertFalse($loader->supports('file.yaml', 'yaml'));

        $loader = new DelegatingLoader(new LoaderRegistry([
            new PhpFileLoader($container, $container),
            new TestLoader(),
        ]));
        self::assertTrue($loader->supports('file.php'));
        self::assertTrue($loader->supports('file.php', 'php'));
        self::assertFalse($loader->supports('file.yaml'));
        self::assertTrue($loader->supports('file.yaml', 'yaml'));
        self::assertFalse($loader->supports('file.yaml', 'php'));
    }
}

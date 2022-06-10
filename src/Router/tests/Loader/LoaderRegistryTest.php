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
        $registry = new LoaderRegistry([
            new PhpFileLoader(new Container()),
            new TestLoader()
        ]);

        $this->assertInstanceOf(PhpFileLoader::class, $registry->resolve('test/file.php'));
        $this->assertInstanceOf(PhpFileLoader::class, $registry->resolve('test/file.php', 'php'));
        $this->assertFalse($registry->resolve('test/file.php', 'yaml'));

        $this->assertInstanceOf(TestLoader::class, $registry->resolve('test/file.yaml'));
        $this->assertInstanceOf(TestLoader::class, $registry->resolve('test/file.yaml', 'yaml'));
        $this->assertFalse($registry->resolve('test/file.yaml', 'php'));
    }

    public function testAddAndGetLoaders(): void
    {
        $registry = new LoaderRegistry();
        $php = new PhpFileLoader(new Container());
        $yaml = new TestLoader();

        $this->assertSame([], $registry->getLoaders());

        $registry->addLoader($php);
        $this->assertSame([$php], $registry->getLoaders());

        $registry->addLoader($yaml);
        $this->assertSame([$php, $yaml], $registry->getLoaders());
    }
}

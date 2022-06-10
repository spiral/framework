<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Loader;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Router\Exception\LoaderLoadException;
use Spiral\Router\Loader\PhpFileLoader;

final class PhpFileLoaderTest extends TestCase
{
    public function testLoad(): void
    {
        $loader = new PhpFileLoader(new Container());

        $this->assertSame('php file', $loader->load(\dirname(__DIR__) . '/Fixtures/file.php'));

        $this->expectException(LoaderLoadException::class);
        $loader->load(\dirname(__DIR__) . '/Fixtures/unknown.php');
    }

    public function testSupports(): void
    {
        $loader = new PhpFileLoader(new Container());

        $this->assertTrue($loader->supports('file.php'));
        $this->assertTrue($loader->supports('file.php', 'php'));

        $this->assertFalse($loader->supports('file.php', 'txt'));
        $this->assertFalse($loader->supports('file.txt'));
        $this->assertFalse($loader->supports('file.txt', 'txt'));
    }
}

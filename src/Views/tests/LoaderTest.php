<?php

declare(strict_types=1);

namespace Spiral\Tests\Views;

use PHPUnit\Framework\TestCase;
use Spiral\Views\Exception\LoaderException;
use Spiral\Views\Exception\PathException;
use Spiral\Views\Loader\PathParser;
use Spiral\Views\ViewLoader;

class LoaderTest extends TestCase
{
    public function testExistsException(): void
    {
        $this->expectException(LoaderException::class);

        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
        ]);

        $loader->exists('view');
    }

    public function testListException(): void
    {
        $this->expectException(LoaderException::class);

        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
        ]);

        $loader->list();
    }

    public function testLoadException(): void
    {
        $this->expectException(LoaderException::class);

        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
        ]);

        $loader->load('view');
    }

    public function testExists(): void
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
        ]);

        $loader = $loader->withExtension('php');

        self::assertFalse($loader->exists('another'));
        self::assertFalse($loader->exists('inner/file.twig'));
        self::assertFalse($loader->exists('inner/file'));

        self::assertTrue($loader->exists('view'));
        self::assertTrue($loader->exists('inner/view'));
        self::assertTrue($loader->exists('inner/partial/view'));

        self::assertTrue($loader->exists('view.php'));

        self::assertTrue($loader->exists('default:view'));
        self::assertTrue($loader->exists('default:view.php'));

        self::assertTrue($loader->exists('@default/view'));
        self::assertTrue($loader->exists('@default/view.php'));

        self::assertTrue($loader->exists('default:inner/partial/view'));
        self::assertTrue($loader->exists('default:inner/partial/view.php'));

        self::assertTrue($loader->exists('@default/inner/partial/view'));
        self::assertTrue($loader->exists('@default/inner/partial/view.php'));
    }

    public function testList(): void
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
        ]);

        $loader = $loader->withExtension('php');
        $files = $loader->list();

        self::assertContains('default:view', $files);
        self::assertContains('default:inner/view', $files);
        self::assertContains('default:inner/partial/view', $files);
        self::assertNotContains('default:inner/file', $files);
    }

    public function testLoadNotFound(): void
    {
        $this->expectException(LoaderException::class);

        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
        ]);

        $loader = $loader->withExtension('php');

        $loader->load('inner/file');
    }

    public function testBadPath(): void
    {
        $this->expectException(PathException::class);

        $parser = new PathParser('default', 'php');
        $parser->parse('@namespace');
    }

    public function testEmptyPath(): void
    {
        $this->expectException(PathException::class);

        $parser = new PathParser('default', 'php');
        $parser->parse('');
    }

    public function testInvalidPath(): void
    {
        $this->expectException(PathException::class);

        $parser = new PathParser('default', 'php');
        $parser->parse("hello\0");
    }

    public function testExternalPath(): void
    {
        $this->expectException(PathException::class);

        $parser = new PathParser('default', 'php');
        $parser->parse('../../index.php');
    }

    public function testLoad(): void
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
        ]);

        $loader = $loader->withExtension('php');

        $source = $loader->load('inner/partial/view');
        self::assertNotNull($source);

        self::assertSame('inner/partial/view', $source->getName());
        self::assertSame('default', $source->getNamespace());
        self::assertFileExists($source->getFilename());

        self::assertSame('hello inner partial world', $source->getCode());

        $newSource = $source->withCode('new code');

        self::assertSame('new code', $newSource->getCode());
        self::assertSame('hello inner partial world', $source->getCode());
    }

    public function testMultipleNamespaces(): void
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
            'other'   => __DIR__ . '/fixtures/other',

        ]);

        $loader = $loader->withExtension('php');

        self::assertTrue($loader->exists('other:view'));
        self::assertFalse($loader->exists('non-existed:view'));

        $files = $loader->list();
        self::assertContains('default:view', $files);
        self::assertContains('other:view', $files);

        $files = $loader->list('other');
        self::assertCount(5, $files);
        self::assertContains('other:view', $files);
    }
}

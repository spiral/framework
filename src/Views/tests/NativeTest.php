<?php

declare(strict_types=1);

namespace Spiral\Tests\Views;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\Exception\EngineException;
use Spiral\Views\Exception\RenderException;
use Spiral\Views\ViewContext;
use Spiral\Views\ViewLoader;

class NativeTest extends TestCase
{
    public function testGet(): void
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
            'other'   => __DIR__ . '/fixtures/other',
        ]);

        $loader = $loader->withExtension('php');

        $engine = new NativeEngine(new Container());
        $engine = $engine->withLoader($loader);

        $engine->reset('other:view', new ViewContext());
        $engine->compile('other:view', new ViewContext());
        $view = $engine->get('other:view', $ctx = new ViewContext());

        self::assertSame('other world', $view->render([]));
    }

    public function testGetNoLoader(): void
    {
        $this->expectException(EngineException::class);

        $engine = new NativeEngine(new Container());
        $engine->get('other:view', new ViewContext());
    }

    public function testRenderWithValue(): void
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
            'other'   => __DIR__ . '/fixtures/other',

        ]);

        $loader = $loader->withExtension('php');

        $engine = new NativeEngine(new Container());
        $engine = $engine->withLoader($loader);

        $view = $engine->get('other:var', $ctx = new ViewContext());
        self::assertSame('hello', $view->render(['value' => 'hello']));
    }

    public function testRenderException(): void
    {
        $this->expectException(RenderException::class);

        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
            'other'   => __DIR__ . '/fixtures/other',

        ]);

        $loader = $loader->withExtension('php');

        $engine = new NativeEngine(new Container());
        $engine = $engine->withLoader($loader);

        $view = $engine->get('other:var', $ctx = new ViewContext());

        $view->render([]);
    }

    public function testRenderBufferWithValue(): void
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
            'other'   => __DIR__ . '/fixtures/other',

        ]);

        $loader = $loader->withExtension('php');

        $engine = new NativeEngine(new Container());
        $engine = $engine->withLoader($loader);

        $view = $engine->get('other:buf', $ctx = new ViewContext());
        self::assertSame('', $view->render(['value' => 'hello']));
    }

    public function testRenderBufferException(): void
    {
        $this->expectException(RenderException::class);

        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
            'other'   => __DIR__ . '/fixtures/other',

        ]);

        $loader = $loader->withExtension('php');

        $engine = new NativeEngine(new Container());
        $engine = $engine->withLoader($loader);

        $view = $engine->get('other:buf', $ctx = new ViewContext());

        $view->render([]);
    }
}

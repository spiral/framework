<?php

declare(strict_types=1);

namespace Spiral\Tests\Views;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Views\Context\ValueDependency;
use Spiral\Views\ContextInterface;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\Exception\CacheException;
use Spiral\Views\ViewCache;
use Spiral\Views\ViewContext;
use Spiral\Views\ViewInterface;
use Spiral\Views\ViewLoader;

class CacheTest extends TestCase
{
    public function testSimpleCache(): void
    {
        $ctx = new ViewContext();
        $cache = new ViewCache();

        $view = $this->getView($ctx, 'default:view');

        $cache->set($ctx, 'default:view', $view);
        self::assertTrue($cache->has($ctx, 'default:view'));
        self::assertSame($view, $cache->get($ctx, 'default:view'));
    }

    public function testGet(): void
    {
        $this->expectException(CacheException::class);

        $cache = new ViewCache();
        $cache->get(new ViewContext(), 'default:view');
    }

    public function testReset(): void
    {
        $ctx = new ViewContext();
        $ctx2 = $ctx->withDependency(new ValueDependency('test', 'value'));

        $cache = new ViewCache();

        $view = $this->getView($ctx, 'default:view');
        $view2 = $this->getView($ctx2, 'other:view');

        $cache->set($ctx, 'default:view', $view);
        $cache->set($ctx2, 'other:view', $view2);

        self::assertTrue($cache->has($ctx, 'default:view'));
        self::assertTrue($cache->has($ctx2, 'other:view'));

        $cache->reset($ctx);

        self::assertFalse($cache->has($ctx, 'default:view'));
        self::assertTrue($cache->has($ctx2, 'other:view'));
    }

    public function testResetAll(): void
    {
        $ctx = new ViewContext();
        $ctx2 = $ctx->withDependency(new ValueDependency('test', 'value'));

        $cache = new ViewCache();

        $view = $this->getView($ctx, 'default:view');
        $view2 = $this->getView($ctx2, 'other:view');

        $cache->set($ctx, 'default:view', $view);
        $cache->set($ctx2, 'other:view', $view2);

        self::assertTrue($cache->has($ctx, 'default:view'));
        self::assertTrue($cache->has($ctx2, 'other:view'));

        $cache->reset();

        self::assertFalse($cache->has($ctx, 'default:view'));
        self::assertFalse($cache->has($ctx2, 'other:view'));
    }

    public function testResetPath(): void
    {
        $ctx = new ViewContext();
        $cache = new ViewCache();

        $view = $this->getView($ctx, 'default:view');
        $view2 = $this->getView($ctx, 'other:view');

        $cache->set($ctx, 'default:view', $view);
        $cache->set($ctx, 'other:view', $view2);

        self::assertTrue($cache->has($ctx, 'default:view'));
        self::assertTrue($cache->has($ctx, 'other:view'));

        $cache->resetPath('other:view');

        self::assertTrue($cache->has($ctx, 'default:view'));
        self::assertFalse($cache->has($ctx, 'other:view'));
    }

    public function testContextValue(): void
    {
        $ctx = new ViewContext();
        $ctx = $ctx->withDependency(new ValueDependency('test', 'value'));

        $cache = new ViewCache();

        $view = $this->getView($ctx, 'default:view');

        $cache->set($ctx, 'default:view', $view);
        self::assertTrue($cache->has($ctx, 'default:view'));

        $ctx = $ctx->withDependency(new ValueDependency('test', 'another'));
        self::assertFalse($cache->has($ctx, 'default:view'));

        $ctx = $ctx->withDependency(new ValueDependency('test', 'value'));
        self::assertTrue($cache->has($ctx, 'default:view'));
    }

    protected function getView(ContextInterface $context, string $path): ViewInterface
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
            'other'   => __DIR__ . '/fixtures/other',
        ]);

        $engine = new NativeEngine(new Container());
        $engine = $engine->withLoader($loader->withExtension('php'));

        return $engine->get($path, $context);
    }
}

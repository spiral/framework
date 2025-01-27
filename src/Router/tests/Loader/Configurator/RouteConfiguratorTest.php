<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Loader\Configurator;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Core\Container;
use Spiral\Core\Core;
use Spiral\Router\Exception\TargetException;
use Spiral\Router\Loader\Configurator\RouteConfigurator;
use Spiral\Router\RouteCollection;
use Spiral\Router\Target\AbstractTarget;
use Spiral\Router\Target\Action;
use Spiral\Router\Target\Controller;
use Spiral\Router\Target\Group;
use Spiral\Router\Target\Namespaced;
use Spiral\Tests\Router\BaseTestCase;

final class RouteConfiguratorTest extends BaseTestCase
{
    public function testDestructException(): void
    {
        $routes = new RouteCollection();

        $configurator = new RouteConfigurator('test', '/', $routes);
        self::assertCount(0, $routes);

        $this->expectException(TargetException::class);
        unset($configurator);
    }

    public function testDestruct(): void
    {
        $routes = new RouteCollection();

        $configurator = new RouteConfigurator('test', '/', $routes);
        self::assertCount(0, $routes);

        $configurator->controller('Controller');

        unset($configurator);

        self::assertCount(1, $routes);
    }

    public function testController(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->controller('SomeController');

        self::assertInstanceOf(Controller::class, $configurator->target);
    }

    public function testNamespaced(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->namespaced('App\\Controller');

        self::assertInstanceOf(Namespaced::class, $configurator->target);
    }

    public function testGroupControllers(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->groupControllers(['Controller']);

        self::assertInstanceOf(Group::class, $configurator->target);
    }

    public function testAction(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->action('Controller', 'action');

        self::assertInstanceOf(Action::class, $configurator->target);
    }

    public function testCallable(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->callable(static fn() => null);

        self::assertInstanceOf(\Closure::class, $configurator->target);
    }

    public function testHandler(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->handler(new class([], []) extends AbstractTarget {
            protected function resolveController(array $matches): string
            {
                return '';
            }

            protected function resolveAction(array $matches): ?string
            {
                return '';
            }
        });

        self::assertInstanceOf(AbstractTarget::class, $configurator->target);
    }

    public function testDefaults(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->controller('Controller')->defaults(['some', 'array']);

        self::assertSame(['some', 'array'], $configurator->defaults);
    }

    public function testGroup(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->controller('Controller')->group('api');

        self::assertSame('api', $configurator->group);
    }

    public function testPrefix(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->controller('Controller')->prefix('admin');

        self::assertSame('admin', $configurator->prefix);
    }

    public function testCore(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->controller('Controller')->core(new Core(new Container()));

        self::assertInstanceOf(Core::class, $configurator->core);
    }

    public function testMiddleware(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->controller('Controller')->middleware('class-string');
        self::assertSame(['class-string'], $configurator->middleware);

        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->controller('Controller')->middleware(['class-string', 'other-class-string']);
        self::assertSame(['class-string', 'other-class-string'], $configurator->middleware);

        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $testMiddleware = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {}
        };
        $configurator->controller('Controller')->middleware($testMiddleware);
        self::assertSame([$testMiddleware], $configurator->middleware);
    }

    public function testMethods(): void
    {
        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->controller('Controller')->methods('GET');
        self::assertSame(['GET'], $configurator->methods);

        $configurator = new RouteConfigurator('test', '/', new RouteCollection());
        $configurator->controller('Controller')->methods(['GET', 'POST']);
        self::assertSame(['GET', 'POST'], $configurator->methods);
    }
}

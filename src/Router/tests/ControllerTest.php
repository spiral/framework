<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Core\CoreInterface;
use Spiral\Http\Exception\ClientException\NotFoundException;
use Spiral\Interceptors\Handler\AutowireHandler;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Router\Exception\UndefinedRouteException;
use Spiral\Router\Route;
use Spiral\Router\Target\Action;
use Spiral\Router\Target\Controller;
use Spiral\Tests\Router\Fixtures\TestController;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;

class ControllerTest extends BaseTestCase
{
    public function testRouteException(): void
    {
        $this->expectException(UndefinedRouteException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>/<id>', new Controller(TestController::class)),
        );

        $router->handle(new ServerRequest('GET', ''));
    }

    public function testRoute(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Controller(TestController::class)),
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/test')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('hello world', (string) $response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/echo')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('echoed', (string) $response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/id/888')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('888', (string) $response->getBody());
    }

    public function testOptionalParam(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/default[/<id>]', new Action(TestController::class, 'default')),
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/default')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('default', (string) $response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/default/123')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('123', (string) $response->getBody());
    }

    public function testFallbackHandler(): void
    {
        $target = new Action(TestController::class, 'default');
        $this->getContainer()->removeBinding(HandlerInterface::class);
        $this->getContainer()->removeBinding(CoreInterface::class);

        $core = $target->getHandler($this->getContainer(), []);
        $handler = (static fn(): HandlerInterface|CoreInterface => $core->core)->bindTo(null, $core)();

        self::assertInstanceOf(AutowireHandler::class, $handler);
    }

    public function testOptionalParamWithDefaultInt(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/defaultInt[/<id>]', new Action(TestController::class, 'defaultInt')),
        );

        $response = $router->handle(new ServerRequest('GET', new Uri('/defaultInt')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('int: 1', (string) $response->getBody());

        $response = $router->handle(new ServerRequest('GET', new Uri('/defaultInt/123')));
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('string: 123', (string) $response->getBody());
    }

    public function testUriGeneration(): void
    {
        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>/<id>', new Controller(TestController::class)),
        );

        $uri = $router->uri('action/test');
        self::assertSame('/test', $uri->getPath());

        $uri = $router->uri('action/id', ['id' => 100]);
        self::assertSame('/id/100', $uri->getPath());
    }

    public function testClientException(): void
    {
        $this->expectException(NotFoundException::class);

        $router = $this->makeRouter();
        $router->setRoute(
            'action',
            new Route('/<action>[/<id>]', new Controller(TestController::class)),
        );

        $router->handle(new ServerRequest('GET', new Uri('/other')));
    }
}

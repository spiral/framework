<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Cocur\Slugify\Slugify;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Uri;
use Spiral\Router\Route;
use Spiral\Router\Router;
use Spiral\Router\Target\Action;
use Spiral\Router\UriHandler;
use Spiral\Telemetry\TracerInterface;
use Spiral\Tests\Router\Diactoros\UriFactory;
use Spiral\Tests\Router\Fixtures\TestController;
use Spiral\Tests\Router\Stub\RecordingTracer;

final class TelemetryTest extends BaseTestCase
{
    public function testRoutingAndControllerSpansAreScoped(): void
    {
        $tracer = new RecordingTracer($this->container);
        $this->container->bind(TracerInterface::class, $tracer);

        $router = new Router(
            '',
            new UriHandler(new UriFactory(), new Slugify()),
            $this->container,
            null,
            $tracer,
        );
        $router->setRoute('action', new Route('/test', new Action(TestController::class, 'test')));

        $router->handle(new ServerRequest('GET', new Uri('/test')));

        self::assertSame([
            'Routing' => true,
            'Controller [' . TestController::class . ':test]' => true,
        ], $tracer->traces);
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Framework\Spiral;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\CurrentRequest;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

#[TestScope(Spiral::Http)]
final class CurrentRequestTest extends HttpTestCase
{
    public function testSetInitialRequest(): void
    {
        $this->setHttpHandler(function (ServerRequestInterface $request): void {
            self::assertSame($request, $this->getContainer()->get(CurrentRequest::class)->get());
        });

        $this->fakeHttp()->get('/')->assertOk();
    }

    public function testAttributesAddedInMiddlewareAreAccessibleInHandler(): void
    {
        $middleware1 = $this->createMiddleware('first', 'first-value');
        $middleware2 = $this->createMiddleware('second', 'second-value');

        $this->getContainer()->bind(HttpConfig::class, new HttpConfig([
            'middleware' => [
                $middleware1::class,
                $middleware2::class,
            ],
            'basePath' => '/',
            'headers' => [],
        ]));

        $this->setHttpHandler(function (ServerRequestInterface $request): void {
            self::assertSame($request, $this->getContainer()->get(CurrentRequest::class)->get());
        });

        $this->fakeHttp()->get('/')->assertOk();
    }

    public function testAttributesAddedInMiddlewareAreAccessibleInNextMiddleware(): void
    {
        $middleware1 = $this->createMiddleware('first', 5);
        $middleware2 = new class($this->getContainer()) implements MiddlewareInterface {
            public function __construct(
                private readonly ContainerInterface $container,
            ) {}

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $initial = $this->container->get(CurrentRequest::class)->get();

                return $handler->handle($request->withAttribute('second', $initial->getAttribute('first') + 5));
            }
        };
        $this->getContainer()->bind($middleware2::class, $middleware2);

        $this->getContainer()->bind(HttpConfig::class, new HttpConfig([
            'middleware' => [
                $middleware1::class,
                $middleware2::class,
            ],
            'basePath' => '/',
            'headers' => [],
        ]));

        $this->setHttpHandler(function (ServerRequestInterface $request): void {
            $current = $this->getContainer()->get(CurrentRequest::class)->get();

            self::assertSame($request, $current);
            self::assertSame(5, $current->getAttribute('first'));
            self::assertSame(10, $current->getAttribute('second'));
        });

        $this->fakeHttp()->get('/')->assertOk();
    }

    private function createMiddleware(string $attribute, mixed $value): MiddlewareInterface
    {
        $middleware = new class($attribute, $value) implements MiddlewareInterface {
            public function __construct(
                private readonly string $attribute,
                private readonly mixed $value,
            ) {}

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle($request->withAttribute($this->attribute, $this->value));
            }
        };

        $this->getContainer()->bind($middleware::class, $middleware);

        return $middleware;
    }
}

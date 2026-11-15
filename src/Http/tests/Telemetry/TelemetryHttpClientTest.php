<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Telemetry;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spiral\Core\Container;
use Spiral\Http\Telemetry\TelemetryHttpClient;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TraceKind;

final class TelemetryHttpClientTest extends TestCase
{
    private const TRACEPARENT = '00-0af7651916cd43dd8448eb211c80319c-b7ad6b7169203331-01';

    public function testRequestIsTracedAndContextIsPropagated(): void
    {
        $inner = new class implements ClientInterface {
            public ?RequestInterface $sent = null;

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->sent = $request;

                return new Response(503);
            }
        };
        $tracer = new RecordingTracer(['traceparent' => self::TRACEPARENT]);

        $response = (new TelemetryHttpClient($inner, $tracer))
            ->sendRequest(new Request('GET', 'http://example.org:8080/users?token=secret'));

        self::assertSame(503, $response->getStatusCode());
        self::assertSame([self::TRACEPARENT], $inner->sent?->getHeader('traceparent'));
        self::assertSame(
            [
                [
                    'name' => 'GET',
                    'kind' => TraceKind::CLIENT,
                    'scoped' => true,
                    'attributes' => [
                        'http.request.method' => 'GET',
                        'url.full' => 'http://example.org:8080/users',
                        'server.address' => 'example.org',
                        'server.port' => 8080,
                        'network.protocol.version' => '1.1',
                        'http.response.status_code' => 503,
                        'error.type' => '503',
                    ],
                    'status' => 'ERROR',
                ],
            ],
            $tracer->spans,
        );
    }

    public function testExceptionIsRethrown(): void
    {
        $exception = new \RuntimeException('Connection refused');
        $inner = new class($exception) implements ClientInterface {
            public function __construct(
                private readonly \Throwable $exception,
            ) {}

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                throw $this->exception;
            }
        };

        $this->expectExceptionObject($exception);

        (new TelemetryHttpClient($inner, new RecordingTracer([])))
            ->sendRequest(new Request('GET', 'http://example.org/'));
    }

    public function testWorksWithContainerScopedTracer(): void
    {
        $inner = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return new Response(204);
            }
        };

        $response = (new TelemetryHttpClient($inner, new NullTracer(new Container())))
            ->sendRequest(new Request('DELETE', 'http://example.org/users/42'));

        self::assertSame(204, $response->getStatusCode());
    }

    public function testRequestAttributesCanBeOverridden(): void
    {
        $inner = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return new Response(200);
            }
        };
        $tracer = new RecordingTracer([]);
        $client = new class($inner, $tracer) extends TelemetryHttpClient {
            protected function getRequestAttributes(RequestInterface $request): array
            {
                $attributes = parent::getRequestAttributes($request);
                $attributes['url.full'] = \preg_replace('#/bot[^/]+/#', '/bot{token}/', $attributes['url.full']);

                return $attributes;
            }
        };

        $client->sendRequest(new Request('POST', 'https://api.telegram.org/bot123:secret/sendMessage'));

        self::assertSame('https://api.telegram.org/bot{token}/sendMessage', $tracer->spans[0]['attributes']['url.full']);
    }
}

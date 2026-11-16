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
    public function testRequestIsTraced(): void
    {
        $inner = new RecordingClient(new Response(503, version: '2'));
        $tracer = new RecordingTracer();

        $response = (new TelemetryHttpClient($inner, $tracer))
            ->sendRequest(new Request('GET', 'http://example.org:8080/users?token=secret'));

        self::assertSame(503, $response->getStatusCode());
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
                        'http.response.status_code' => 503,
                        'network.protocol.version' => '2',
                        'error.type' => '503',
                    ],
                    'status' => 'ERROR',
                    'description' => null,
                ],
            ],
            $tracer->spans,
        );
    }

    public function testContextOfClientSpanIsPropagated(): void
    {
        $inner = new RecordingClient(new Response(200));
        $tracer = new RecordingTracer();
        $client = new TelemetryHttpClient($inner, $tracer);

        $tracer->trace('request', static fn() => $client->sendRequest(
            new Request('GET', 'http://example.org/', ['traceparent' => 'stale']),
        ));

        self::assertSame(['request/GET'], $inner->sent?->getHeader('traceparent'));
    }

    public function testExceptionMarksSpanAndIsRethrown(): void
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
        $tracer = new RecordingTracer();

        try {
            (new TelemetryHttpClient($inner, $tracer))->sendRequest(new Request('GET', 'http://example.org/'));
            self::fail('The exception was not rethrown.');
        } catch (\RuntimeException $e) {
            self::assertSame($exception, $e);
        }

        self::assertCount(1, $tracer->spans);
        self::assertSame('ERROR', $tracer->spans[0]['status']);
        self::assertSame('Connection refused', $tracer->spans[0]['description']);
        self::assertSame(\RuntimeException::class, $tracer->spans[0]['attributes']['error.type']);
        self::assertArrayNotHasKey('http.response.status_code', $tracer->spans[0]['attributes']);
    }

    public function testWorksWithContainerScopedTracer(): void
    {
        $inner = new RecordingClient(new Response(204));

        $response = (new TelemetryHttpClient($inner, new NullTracer(new Container())))
            ->sendRequest(new Request('DELETE', 'http://example.org/users/42'));

        self::assertSame(204, $response->getStatusCode());
        self::assertSame(['Host' => ['example.org']], $inner->sent?->getHeaders());
    }

    public function testRequestAttributesCanBeOverridden(): void
    {
        $inner = new RecordingClient(new Response(200));
        $tracer = new RecordingTracer();
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

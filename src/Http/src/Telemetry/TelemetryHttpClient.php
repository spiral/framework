<?php

declare(strict_types=1);

namespace Spiral\Http\Telemetry;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TraceKind;
use Spiral\Telemetry\TracerInterface;

/**
 * PSR-18 client decorator: wraps every outgoing request into a CLIENT span and propagates the trace context
 * (e.g. `traceparent`) to the remote service through the request headers, so both sides end up in one trace.
 *
 * Requires `psr/http-client`. To change what the span captures, extend it and override
 * {@see getRequestAttributes()}.
 *
 * Every header of the tracer context goes to the remote service, which may be a third party: with the OpenTelemetry
 * driver that includes `baggage`. A header of the same name already set on the request is replaced.
 *
 * The span describes the request as it reaches the decorator, so send absolute URIs. A client that resolves
 * a relative one against its base URI (Guzzle's `base_uri`) does that later: the span then gets a relative
 * `url.full`, and `server.address` and `server.port` only from an explicit `Host` header.
 *
 * Do not combine it with the OpenTelemetry PSR-18 auto-instrumentation (`open-telemetry/opentelemetry-auto-psr18`):
 * each request would get two nested CLIENT spans.
 *
 * Example:
 *
 *     final class TelegramHttpClient extends TelemetryHttpClient
 *     {
 *         // The Telegram Bot API puts the bot token into the URL path
 *         protected function getRequestAttributes(RequestInterface $request): array
 *         {
 *             $attributes = parent::getRequestAttributes($request);
 *             unset($attributes['url.full']);
 *
 *             return $attributes;
 *         }
 *     }
 */
class TelemetryHttpClient implements ClientInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly TracerInterface $tracer,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        /** @var ResponseInterface */
        return $this->tracer->trace(
            name: HttpSpanAttributes::clientSpanName($request),
            callback: function (SpanInterface $span) use ($request): ResponseInterface {
                // Read inside the span: the remote side has to continue from the client span, not its parent.
                foreach ($this->tracer->getContext() as $header => $value) {
                    $request = $request->withHeader($header, $value);
                }

                try {
                    $response = $this->client->sendRequest($request);
                } catch (\Throwable $e) {
                    HttpSpanAttributes::applyClientException($span, $e);
                    throw $e;
                }

                HttpSpanAttributes::applyClientResponse($span, $response);

                return $response;
            },
            attributes: $this->getRequestAttributes($request),
            scoped: true,
            traceKind: TraceKind::CLIENT,
        );
    }

    /**
     * Attributes the span starts with.
     *
     * Spans are exported to a tracing backend and are readable by everyone who has access to it, so they must not
     * carry secrets. Headers, user info and the query string are already left out, but some APIs put a secret
     * into the URL path (e.g. the Telegram Bot API token). Override this method to redact or drop such data,
     * see the example in the class description.
     *
     * @return array<non-empty-string, mixed>
     */
    protected function getRequestAttributes(RequestInterface $request): array
    {
        return HttpSpanAttributes::clientRequest($request);
    }
}

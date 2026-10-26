<?php

declare(strict_types=1);

namespace Spiral\Http\Telemetry;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Telemetry\SpanInterface;

/**
 * Builds span names and attributes that follow the stable OpenTelemetry HTTP semantic conventions.
 *
 * @see https://opentelemetry.io/docs/specs/semconv/http/http-spans/
 *
 * @internal
 */
final class HttpSpanAttributes
{
    private const METHOD_OTHER = '_OTHER';
    private const KNOWN_METHODS = ['CONNECT', 'DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT', 'QUERY', 'TRACE'];

    /**
     * @return non-empty-string The method as is when it is known, `_OTHER` otherwise.
     */
    public static function method(string $method): string
    {
        return \in_array($method, self::KNOWN_METHODS, true) ? $method : self::METHOD_OTHER;
    }

    /**
     * The route is not known when the server span starts, and the URL must not be used as a low-cardinality
     * target, so the name is the method alone.
     *
     * @return non-empty-string
     */
    public static function serverSpanName(ServerRequestInterface $request): string
    {
        $method = self::method($request->getMethod());

        return $method === self::METHOD_OTHER ? 'HTTP' : $method;
    }

    /**
     * Request headers are not captured: they carry credentials such as `Authorization` and `Cookie`.
     *
     * @return array<non-empty-string, string|int>
     */
    public static function serverRequest(ServerRequestInterface $request): array
    {
        $uri = $request->getUri();
        $method = self::method($request->getMethod());

        $attributes = [
            'http.request.method' => $method,
            'url.scheme' => $uri->getScheme(),
            'url.path' => $uri->getPath() === '' ? '/' : $uri->getPath(),
        ];

        if ($method !== $request->getMethod()) {
            $attributes['http.request.method_original'] = $request->getMethod();
        }

        if ($uri->getQuery() !== '') {
            $attributes['url.query'] = $uri->getQuery();
        }

        if ($uri->getHost() !== '') {
            $attributes['server.address'] = $uri->getHost();
            $port = $uri->getPort() ?? match ($uri->getScheme()) {
                'http' => 80,
                'https' => 443,
                default => null,
            };
            if ($port !== null) {
                $attributes['server.port'] = $port;
            }
        }

        $clientAddress = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        if (\is_string($clientAddress) && $clientAddress !== '') {
            $attributes['client.address'] = $clientAddress;
        }

        $userAgent = $request->getHeaderLine('User-Agent');
        if ($userAgent !== '') {
            $attributes['user_agent.original'] = $userAgent;
        }

        if ($request->getProtocolVersion() !== '') {
            $attributes['network.protocol.version'] = $request->getProtocolVersion();
        }

        return $attributes;
    }

    /**
     * Only a 5xx response marks the span as failed; below that the status stays unset, because a 4xx is the
     * client's error and instrumentation must not set `OK`.
     */
    public static function applyResponse(SpanInterface $span, ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $span->setAttribute('http.response.status_code', $statusCode);

        $contentLength = $response->getHeaderLine('Content-Length');
        $bodySize = \preg_match('/^\d+$/', $contentLength) === 1 ? (int) $contentLength : $response->getBody()->getSize();
        if ($bodySize !== null) {
            $span->setAttribute('http.response.body.size', $bodySize);
        }

        if ($statusCode >= 500) {
            $span
                ->setAttribute('error.type', (string) $statusCode)
                ->setStatus('ERROR');
        }
    }
}

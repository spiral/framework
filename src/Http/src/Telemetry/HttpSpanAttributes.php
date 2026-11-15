<?php

declare(strict_types=1);

namespace Spiral\Http\Telemetry;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Telemetry\SpanInterface;

/**
 * Builds span names and attributes that follow the stable OpenTelemetry HTTP semantic conventions.
 *
 * @see https://opentelemetry.io/docs/specs/semconv/http/http-spans/
 *
 * @psalm-type TClientRequestAttributes = array{
 *     'http.request.method': non-empty-string,
 *     'url.full': string,
 *     'http.request.method_original'?: string,
 *     'server.address'?: non-empty-string,
 *     'server.port'?: int,
 *     'network.protocol.version'?: non-empty-string,
 * }
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
        return self::spanName($request->getMethod());
    }

    /**
     * The URL template is not known to a client, so the name is the method alone.
     *
     * @return non-empty-string
     */
    public static function clientSpanName(RequestInterface $request): string
    {
        return self::spanName($request->getMethod());
    }

    /**
     * Request headers, user info and the query string are not captured: they carry credentials, API keys and
     * signatures of pre-signed URLs.
     *
     * @return TClientRequestAttributes
     */
    public static function clientRequest(RequestInterface $request): array
    {
        $uri = $request->getUri();
        $method = self::method($request->getMethod());

        $attributes = [
            'http.request.method' => $method,
            'url.full' => (string) $uri->withUserInfo('')->withQuery('')->withFragment(''),
        ];

        if ($method !== $request->getMethod()) {
            $attributes['http.request.method_original'] = $request->getMethod();
        }

        $host = $uri->getHost();
        if ($host !== '') {
            $attributes['server.address'] = $host;
            $port = self::port($uri);
            if ($port !== null) {
                $attributes['server.port'] = $port;
            }
        }

        $protocolVersion = $request->getProtocolVersion();
        if ($protocolVersion !== '') {
            $attributes['network.protocol.version'] = $protocolVersion;
        }

        return $attributes;
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
            $port = self::port($uri);
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

    /**
     * Unlike a server span, a client span marks a 4xx response as failed too: the request did not succeed.
     */
    public static function applyClientResponse(SpanInterface $span, ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $span->setAttribute('http.response.status_code', $statusCode);

        if ($statusCode >= 400) {
            $span
                ->setAttribute('error.type', (string) $statusCode)
                ->setStatus('ERROR');
        }
    }

    /**
     * @return non-empty-string
     */
    private static function spanName(string $method): string
    {
        $method = self::method($method);

        return $method === self::METHOD_OTHER ? 'HTTP' : $method;
    }

    private static function port(UriInterface $uri): ?int
    {
        return $uri->getPort() ?? match ($uri->getScheme()) {
            'http' => 80,
            'https' => 443,
            default => null,
        };
    }
}

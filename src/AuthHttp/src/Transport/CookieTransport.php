<?php

declare(strict_types=1);

namespace Spiral\Auth\Transport;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Auth\HttpTransportInterface;
use Spiral\Cookies\Cookie;
use Spiral\Cookies\CookieQueue;

/**
 * Stores auth tokens in cookies.
 */
final class CookieTransport implements HttpTransportInterface
{
    public function __construct(
        private readonly string $cookie,
        private readonly string $basePath = '/',
        private readonly ?string $domain = null,
        private readonly bool $secure = false,
        private readonly bool $httpOnly = true,
        private readonly ?string $sameSite = null
    ) {
    }

    public function fetchToken(Request $request): ?string
    {
        $cookies = $request->getCookieParams();
        return $cookies[$this->cookie] ?? null;
    }

    public function commitToken(
        Request $request,
        Response $response,
        string $tokenID = null,
        \DateTimeInterface $expiresAt = null
    ): Response {
        /** @var CookieQueue $cookieQueue */
        $cookieQueue = $request->getAttribute(CookieQueue::ATTRIBUTE);
        if ($cookieQueue === null) {
            return $response->withAddedHeader(
                'Set-Cookie',
                Cookie::create(
                    $this->cookie,
                    $tokenID,
                    $this->getLifetime($expiresAt),
                    $this->basePath,
                    $this->domain,
                    $this->secure,
                    $this->httpOnly,
                    $this->sameSite
                )->createHeader()
            );
        }

        if ($tokenID === null) {
            $cookieQueue->delete($this->cookie);
        } else {
            $cookieQueue->set(
                $this->cookie,
                $tokenID,
                $this->getLifetime($expiresAt),
                $this->basePath,
                $this->domain,
                $this->secure,
                $this->httpOnly,
                $this->sameSite
            );
        }

        return $response;
    }

    public function removeToken(Request $request, Response $response, string $tokenID): Response
    {
        // reset to null
        return $this->commitToken($request, $response, null, null);
    }

    /**
     * @return int<0, max>|null
     */
    private function getLifetime(\DateTimeInterface $expiresAt = null): ?int
    {
        if ($expiresAt === null) {
            return null;
        }

        return max($expiresAt->getTimestamp() - time(), 0);
    }
}

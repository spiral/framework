<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 * @author  Valentin V (vvval)
 */

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
    /** @var string */
    private $cookie;

    /** @var string */
    private $basePath;

    /** @var string|null */
    private $domain;

    /** @var bool */
    private $secure;

    /** @var bool */
    private $httpOnly;

    /** @var string|null */
    private $sameSite;

    /**
     * @param string      $cookie
     * @param string      $basePath
     * @param string|null $domain
     * @param bool        $secure
     * @param bool        $httpOnly
     * @param string|null $sameSite
     */
    public function __construct(
        string $cookie,
        string $basePath = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        ?string $sameSite = null
    ) {
        $this->cookie = $cookie;
        $this->basePath = $basePath;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
    }

    /**
     * @inheritDoc
     */
    public function fetchToken(Request $request): ?string
    {
        $cookies = $request->getCookieParams();
        return $cookies[$this->cookie] ?? null;
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function removeToken(Request $request, Response $response, string $tokenID): Response
    {
        // reset to null
        return $this->commitToken($request, $response, null, null);
    }

    /**
     * @param \DateTimeInterface|null $expiresAt
     * @return int|null
     */
    private function getLifetime(\DateTimeInterface $expiresAt = null): ?int
    {
        if ($expiresAt === null) {
            return null;
        }

        return max($expiresAt->getTimestamp() - time(), 0);
    }
}

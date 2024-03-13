<?php

declare(strict_types=1);

namespace Spiral\Session\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Spiral\Cookies\Config\CookiesConfig;
use Spiral\Cookies\Cookie;
use Spiral\Core\ScopeInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Session\Config\SessionConfig;
use Spiral\Session\SessionFactoryInterface;
use Spiral\Session\SessionInterface;

final class SessionMiddleware implements MiddlewareInterface
{
    // request attribute
    public const ATTRIBUTE = 'session';

    // Header set used to sign session
    private const SIGNATURE_HEADERS = ['User-Agent', 'Accept-Language', 'Accept-Encoding'];

    /**
     * @param ScopeInterface $scope Deprecated, will be removed in v4.0.
     */
    public function __construct(
        private readonly SessionConfig $config,
        private readonly HttpConfig $httpConfig,
        private readonly CookiesConfig $cookiesConfig,
        private readonly SessionFactoryInterface $factory,
        private readonly ScopeInterface $scope
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function process(Request $request, Handler $handler): Response
    {
        //Initiating session, this can only be done once!
        $session = $this->factory->initSession(
            $this->clientSignature($request),
            $this->fetchID($request)
        );

        try {
            $response = $handler->handle($request->withAttribute(self::ATTRIBUTE, $session));
        } catch (\Throwable $e) {
            $session->abort();
            throw $e;
        }

        return $this->commitSession($session, $request, $response);
    }

    protected function commitSession(
        SessionInterface $session,
        Request $request,
        Response $response
    ): Response {
        if (!$session->isStarted()) {
            return $response;
        }

        $session->commit();

        //SID changed
        if ($this->fetchID($request) !== $session->getID()) {
            return $this->withCookie($request, $response, $session->getID());
        }

        //Nothing to do
        return $response;
    }

    /**
     * Attempt to locate session ID in request.
     */
    protected function fetchID(Request $request): ?string
    {
        $cookies = $request->getCookieParams();
        if (empty($cookies[$this->config->getCookie()])) {
            return null;
        }

        return $cookies[$this->config->getCookie()];
    }

    protected function withCookie(Request $request, Response $response, string $id = null): Response
    {
        return $response->withAddedHeader(
            'Set-Cookie',
            $this->sessionCookie($request->getUri(), $id)->createHeader()
        );
    }

    /**
     * Must return string which identifies client on other end. Not for security check but for
     * session fixation.
     */
    protected function clientSignature(Request $request): string
    {
        $signature = '';
        foreach (self::SIGNATURE_HEADERS as $header) {
            $signature .= $request->getHeaderLine($header) . ';';
        }

        return \hash('sha256', $signature);
    }

    /**
     * Generate session cookie.
     *
     * @param UriInterface $uri Incoming uri.
     */
    private function sessionCookie(UriInterface $uri, string $id = null): Cookie
    {
        return Cookie::create(
            $this->config->getCookie(),
            $id,
            $this->config->getLifetime(),
            $this->httpConfig->getBasePath(),
            $this->cookiesConfig->resolveDomain($uri),
            $this->config->isSecure(),
            true,
            $this->config->getSameSite()
        );
    }
}

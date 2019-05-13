<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Http\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Spiral\Core\ScopeInterface;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Cookie\Cookie;
use Spiral\Session\Config\SessionConfig;
use Spiral\Session\SessionFactory;
use Spiral\Session\SessionInterface;

final class SessionMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE = 'session';

    // Header set used to sign session
    protected const SIGNATURE_HEADERS = ['User-Agent', 'Accept-Language', 'Accept-Encoding'];

    /** @var SessionConfig */
    private $config;

    /** @var HttpConfig */
    private $httpConfig;

    /** @var SessionFactory */
    private $factory;

    /** @var ScopeInterface */
    private $scope;

    /**
     * @param SessionConfig  $config
     * @param HttpConfig     $httpConfig
     * @param SessionFactory $factory
     * @param ScopeInterface $scope
     */
    public function __construct(
        SessionConfig $config,
        HttpConfig $httpConfig,
        SessionFactory $factory,
        ScopeInterface $scope
    ) {
        $this->config = $config;
        $this->httpConfig = $httpConfig;
        $this->factory = $factory;
        $this->scope = $scope;
    }

    /**
     * @inheritdoc
     */
    public function process(Request $request, Handler $handler): Response
    {
        //Initiating session, this can only be done once!
        $session = $this->factory->initSession(
            $this->clientSignature($request),
            $this->fetchID($request)
        );

        try {
            $response = $this->scope->runScope(
                [SessionInterface::class => $session],
                function () use ($session, $request, $handler) {
                    return $handler->handle($request->withAttribute(static::ATTRIBUTE, $session));
                }
            );
        } catch (\Throwable $e) {
            $session->abort();
            throw $e;
        }

        return $this->commitSession($session, $request, $response);
    }

    /**
     * @param SessionInterface $session
     * @param Request          $request
     * @param Response         $response
     * @return Response
     */
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
        if ($this->fetchID($request) != $session->getID()) {
            return $this->withCookie($request, $response, $session->getID());
        }

        //Nothing to do
        return $response;
    }

    /**
     * Attempt to locate session ID in request.
     *
     * @param Request $request
     * @return string|null
     */
    protected function fetchID(Request $request): ?string
    {
        $cookies = $request->getCookieParams();
        if (empty($cookies[$this->config->getCookie()])) {
            return null;
        }

        return $cookies[$this->config->getCookie()];
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param string   $id
     * @return Response
     */
    protected function withCookie(Request $request, Response $response, string $id = null): Response
    {
        $response = $response->withAddedHeader(
            'Set-Cookie',
            $this->sessionCookie($request->getUri(), $id)->createHeader()
        );

        return $response;
    }

    /**
     * Must return string which identifies client on other end. Not for security check but for
     * session fixation.
     *
     * @param Request $request
     * @return string
     */
    protected function clientSignature(Request $request): string
    {
        $signature = '';
        foreach (static::SIGNATURE_HEADERS as $header) {
            $signature .= $request->getHeaderLine($header) . ';';
        }

        return hash('sha256', $signature);
    }

    /**
     * Generate session cookie.
     *
     * @param UriInterface $uri Incoming uri.
     * @param string|null  $id
     * @return Cookie
     */
    private function sessionCookie(UriInterface $uri, string $id = null): Cookie
    {
        return Cookie::create(
            $this->config->getCookie(),
            $id,
            $this->config->getLifetime(),
            $this->httpConfig->basePath(),
            $this->httpConfig->cookieDomain($uri),
            $this->config->isSecure(),
            true
        );
    }
}
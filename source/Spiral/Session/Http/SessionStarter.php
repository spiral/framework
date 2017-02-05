<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Session\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Cookies\Cookie;
use Spiral\Http\Cookies\CookieQueue;
use Spiral\Http\MiddlewareInterface;
use Spiral\Session\Configs\SessionConfig;
use Spiral\Session\SessionFactory;
use Spiral\Session\SessionInterface;

/**
 * HttpMiddleware used to create and commit session data using cookies as sessionID provider.
 * Middleware can not work in nested queries.
 *
 * Note: each session is signed based on user headers.
 */
class SessionStarter implements MiddlewareInterface
{
    const ATTRIBUTE = 'session';

    /**
     * @var \Spiral\Session\Configs\SessionConfig
     */
    private $config;

    /**
     * @var \Spiral\Http\Configs\HttpConfig
     */
    private $httpConfig;

    /**
     * @var \Spiral\Session\SessionFactory
     */
    private $factory;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param SessionConfig      $config
     * @param HttpConfig         $httpConfig
     * @param SessionFactory     $factory
     * @param ContainerInterface $container
     */
    public function __construct(
        SessionConfig $config,
        HttpConfig $httpConfig,
        SessionFactory $factory,
        ContainerInterface $container
    ) {
        $this->config = $config;
        $this->httpConfig = $httpConfig;

        $this->factory = $factory;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        //Initiating session, this can only be done once!
        $session = $this->factory->initSession(
            $this->fetchSignature($request),
            $this->fetchID($request)
        );

        $scope = $this->container->replace(SessionInterface::class, $session);
        try {
            //Inner application get session scope via container and request
            $response = $next(
                $request->withAttribute(static::ATTRIBUTE, $session),
                $response
            );
        } finally {
            $this->container->restore($scope);
        }

        return $this->closeSession($session, $request, $response);
    }

    /**
     * @param SessionInterface $session
     * @param Request          $request
     * @param Response         $response
     *
     * @return Response
     */
    protected function closeSession(
        SessionInterface $session,
        Request $request,
        Response $response
    ): Response {
        //Commit session data (if session active)
        $session->commit();

        if ($this->fetchID($request) != $session->getID()) {
            //SID changed
            return $this->mountCookie($request, $response, $session->getID());
        }

        //Nothing to do
        return $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param string   $sessionID
     *
     * @return Response
     */
    protected function mountCookie(
        Request $request,
        Response $response,
        string $sessionID = null
    ): Response {
        $cookie = $this->sessionCookie($request->getUri(), $sessionID);

        if (!empty($queue = $request->getAttribute(CookieQueue::ATTRIBUTE))) {
            /** @var CookieQueue $queue */
            $queue->schedule($cookie);
        } else {
            //Fallback, this is less secure but faster way
            $response = $response->withAddedHeader('Set-Cookie', (string)$cookie);
        }

        return $response;
    }

    /**
     * Attempt to locate session ID in request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected function fetchID(Request $request)
    {
        $cookies = $request->getCookieParams();

        if (empty($cookies[$this->config->sessionCookie()])) {
            return null;
        }

        return $cookies[$this->config->sessionCookie()];
    }

    /**
     * Must return string which identifies client on other end. Not for security check but for
     * session fixation.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function fetchSignature(Request $request): string
    {
        $signature = '';

        foreach ($this->config->signHeaders() as $header) {
            $signature .= $request->getHeaderLine($header) . ';';
        }

        return hash('sha256', $signature);
    }

    /**
     * Generate session cookie.
     *
     * @param UriInterface $uri Incoming uri.
     * @param string|null  $sessionID
     *
     * @return Cookie
     */
    private function sessionCookie(UriInterface $uri, $sessionID)
    {
        return Cookie::create(
            $this->config->sessionCookie(),
            $sessionID,
            $this->config->sessionLifetime(),
            $this->httpConfig->basePath(),
            $this->httpConfig->cookiesDomain($uri),
            $this->config->sessionSecure(),
            true
        );
    }
}
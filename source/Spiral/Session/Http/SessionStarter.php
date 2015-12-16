<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Session\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Spiral\Cache\StoreInterface;
use Spiral\Core\ContainerInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Cookies\Cookie;
use Spiral\Http\MiddlewareInterface;
use Spiral\Session\Configs\SessionConfig;
use Spiral\Session\SessionInterface;
use Spiral\Session\SessionStore;

/**
 * HttpMiddleware used to create and commit session data using cookies as sessionID provider.
 * Expected to work with SessionStore class.
 *
 * Attention, nested sessions are not well isolated at this moment as native php session mechanism
 * used (to be changed over time).
 */
class SessionStarter implements MiddlewareInterface
{
    /**
     * @var SessionConfig
     */
    protected $config = null;

    /**
     * @var HttpConfig
     */
    protected $httpConfig = null;

    /**
     * @var SessionStore
     */
    protected $session = null;

    /**
     * @param SessionConfig $config
     * @param HttpConfig    $httpConfig
     * @param SessionStore  $session
     */
    public function __construct(
        SessionConfig $config,
        HttpConfig $httpConfig,
        SessionStore $session
    ) {
        $this->config = $config;
        $this->httpConfig = $httpConfig;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $this->initSession($request);

        $response = $next(
            $request->withAttribute('session', $this->session),
            $response
        );

        return $this->commitSession($request, $response);
    }

    /**
     * Initiate session.
     *
     * @param Request $request
     */
    protected function initSession(Request $request)
    {
        if (!empty($sessionID = $this->fetchSID($request))) {
            //No automatic start
            $this->session->setID($sessionID, false);
        }
    }

    /**
     * Mount session id or remove session cookie.
     *
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    protected function commitSession(Request $request, Response $response)
    {
        if (!$this->session->isStarted()) {
            //Nothing to do
            return $response;
        }

        //Incoming sessionID
        $sessionID = $this->fetchSID($request);

        if (empty($sessionID) || $sessionID != $this->session->getID(false)) {
            //Let's mount cookie
            $response = $response->withAddedHeader(
                'Set-Cookie',
                $this->sessionCookie(
                    $request->getUri(),
                    $this->session->getID(false))->createHeader()
            );
        }

        $this->session->commit();

        return $response;
    }

    /**
     * Fetch sessionID from request or return null.
     *
     * @param Request $request
     * @return string|null
     */
    protected function fetchSID(Request $request)
    {
        $cookies = $request->getCookieParams();

        if (empty($cookies[$this->config->sessionCookie()])) {
            return null;
        }

        return $cookies[$this->config->sessionCookie()];
    }

    /**
     * Generate session cookie.
     *
     * @param UriInterface $uri Incoming uri.
     * @param string       $sessionID
     * @return Cookie
     */
    private function sessionCookie(UriInterface $uri, $sessionID)
    {
        return Cookie::create(
            $this->config->sessionCookie(),
            $sessionID,
            $this->config->sessionLifetime(),
            $this->httpConfig->basePath(),          //todo: to be fetched from request
            $this->httpConfig->cookiesDomain($uri)  //todo: to be fetched from request and set by?
        );
    }
}
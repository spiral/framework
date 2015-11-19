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
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Cookies\Cookie;
use Spiral\Http\MiddlewareInterface;
use Spiral\Session\Configs\SessionConfig;
use Spiral\Session\SessionInterface;

/**
 * HttpMiddleware used to create and commit session data using cookies as sessionID provider.
 * Expected to work with SessionStore class.
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
     * @var SessionInterface
     */
    protected $session = null;

    /**
     * @param SessionConfig    $config
     * @param HttpConfig       $httpConfig
     * @param SessionInterface $session
     */
    public function __construct(
        SessionConfig $config,
        HttpConfig $httpConfig,
        SessionInterface $session
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
        $response = $next($request, $response);

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
        if (!$this->session->started()) {
            //Nothing to do
            return $response;
        }

        //Incoming sessionID
        $sessionID = $this->fetchSID($request);

        if (empty($sessionID) || $sessionID != $this->session->getID(false)) {
            //Let's mount cookie
            $response = $response->withAddedHeader(
                'Set-Cookie',
                $this->sessionCookie($request->getUri(), $this->session->getID(false))->packHeader()
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
    protected function sessionCookie(UriInterface $uri, $sessionID)
    {
        return Cookie::create(
            $this->config->sessionCookie(),
            $sessionID,
            $this->config->sessionLifetime(),
            $this->httpConfig->basePath(),
            $this->httpConfig->cookiesDomain($uri)
        );
    }
}
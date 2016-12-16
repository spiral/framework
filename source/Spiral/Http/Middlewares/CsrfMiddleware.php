<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UriInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Cookies\Cookie;
use Spiral\Http\MiddlewareInterface;

/**
 * Provides generic CSRF protection using cookie as token storage. Set "csrfToken" attribute to
 * request.
 */
class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * Request attribute value.
     */
    const ATTRIBUTE = 'csrfToken';

    /**
     * @var HttpConfig
     */
    protected $httpConfig = null;

    /**
     * @param HttpConfig $httpConfig
     */
    public function __construct(HttpConfig $httpConfig)
    {
        $this->httpConfig = $httpConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (isset($request->getCookieParams()[$this->httpConfig->csrfCookie()])) {
            $token = $request->getCookieParams()[$this->httpConfig->csrfCookie()];
        } else {
            //Making new token
            $token = $this->generateToken();

            //Token cookie!
            $cookie = $this->tokenCookie($request->getUri(), $token);

            //We can alter response cookies
            $response = $response->withAddedHeader('Set-Cookie', $cookie->createHeader());
        }

        //CSRF inssues must be handled by Firewall middleware
        return $next($request->withAttribute(static::ATTRIBUTE, $token), $response);
    }

    /**
     * Generate CSRF token (does not replace anything, only generates are value).
     *
     * @return string
     */
    public function generateToken(): string
    {
        return substr(
            base64_encode(openssl_random_pseudo_bytes($this->httpConfig->csrfLength())), 0,
            $this->httpConfig->csrfLength()
        );
    }

    /**
     * Generate CSRF cookie.
     *
     * @param UriInterface $uri Incoming uri.
     * @param string       $token
     *
     * @return Cookie
     */
    protected function tokenCookie(UriInterface $uri, string $token): Cookie
    {
        return Cookie::create(
            $this->httpConfig->csrfCookie(),
            $token,
            $this->httpConfig->csrfLifetime(),
            $this->httpConfig->basePath(),
            $this->httpConfig->cookiesDomain($uri)
        );
    }
}
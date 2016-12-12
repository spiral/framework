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
class CsrfFilter implements MiddlewareInterface
{
    /**
     * Header to check for token instead of POST/GET data.
     */
    const HEADER = 'X-CSRF-Token';

    /**
     * Parameter name used to represent client token in POST data.
     */
    const PARAMETER = 'csrf-token';

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

        if ($this->isRequired($request) && !$this->compare($token, $this->fetchToken($request))) {
            //Invalid CSRF token
            return $response->withStatus(412, 'Bad CSRF Token');
        }

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
     * Check if middleware should validate csrf token.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isRequired(Request $request): bool
    {
        return !in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS']);
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

    /**
     * Fetch token from request.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function fetchToken(Request $request): string
    {
        if ($request->hasHeader(self::HEADER)) {
            return (string)$request->getHeaderLine(self::HEADER);
        }

        $data = $request->getParsedBody();
        if (is_array($data) && isset($data[self::PARAMETER])) {
            if (is_string($data[self::PARAMETER])) {
                return (string)$data[self::PARAMETER];
            }
        }

        return '';
    }

    /**
     * Perform timing attack safe string comparison of tokens.
     *
     * @link http://blog.ircmaxell.com/2014/11/its-all-about-time.html
     *
     * @param string $token Known token.
     * @param string $clientToken
     *
     * @return bool
     */
    protected function compare(string $token, string $clientToken): bool
    {
        if (function_exists('hash_compare')) {
            return hash_compare($token, $clientToken);
        }

        $tokenLength = strlen($token);
        $clientLength = strlen($clientToken);

        if ($clientLength != $tokenLength) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < $clientLength; $i++) {
            $result = $result | (ord($token[$i]) ^ ord($clientToken[$i]));
        }

        return $result === 0;
    }
}
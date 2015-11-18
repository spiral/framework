<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\Cookies\Cookie;
use Spiral\Http\MiddlewareInterface;

/**
 * Provides generic CSRF protection using cookie as token storage. Set "csrfToken" attribute to
 * request.
 */
class CsrfFilter implements MiddlewareInterface
{
    /**
     * Token have to check in cookies and queries.
     */
    const COOKIE = 'csrf-token';

    /**
     * CSRF token length.
     */
    const TOKEN_LENGTH = 16;

    /**
     * Verification cookie lifetime.
     */
    const LIFETIME = 86400;

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
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        if (isset($request->getCookieParams()[self::COOKIE])) {
            $token = $request->getCookieParams()[self::COOKIE];
        } else {
            //Making new token
            $token = substr(
                base64_encode(openssl_random_pseudo_bytes(self::TOKEN_LENGTH)), 0,
                self::TOKEN_LENGTH
            );

            //We can alter response cookies
            $response = $response->withAddedHeader(
                'Set-Cookie',
                Cookie::create(
                    self::COOKIE,
                    $token,
                    self::LIFETIME,
                    $request->getAttribute('basePath'),
                    $request->getAttribute('cookieDomain')
                )->packHeader()
            );
        }

        if ($this->isRequired($request) && !$this->compare($token, $this->fetchToken($request))) {
            //Invalid CSRF token
            return $response->withStatus(412, 'Bad CSRF Token');
        }

        return $next(
            $request->withAttribute(static::ATTRIBUTE, $token),
            $response
        );
    }

    /**
     * Check if middleware should validate csrf token.
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isRequired(ServerRequestInterface $request)
    {
        return !in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS']);
    }

    /**
     * Fetch token from request.
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function fetchToken(ServerRequestInterface $request)
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
     * @param string $token Known token.
     * @param string $clientToken
     * @return bool
     */
    protected function compare($token, $clientToken)
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
            $result |= (ord($token[$i]) ^ ord($clientToken[$i]));
        }

        return $result === 0;
    }
}
<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Csrf\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Cookies\Cookie;
use Spiral\Csrf\Config\CsrfConfig;

/**
 * Provides generic CSRF protection using cookie as token storage. Set "csrfToken" attribute to
 * request.
 *
 * Do not use middleware without CookieManager at top!
 *
 * @see https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)_Prevention_Cheat_Sheet#Double_Submit_Cookie
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE = 'csrfToken';

    /** @var CsrfConfig */
    protected $config;

    /**
     * @param CsrfConfig $config
     */
    public function __construct(CsrfConfig $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if (isset($request->getCookieParams()[$this->config->getCookie()])) {
            $token = $request->getCookieParams()[$this->config->getCookie()];
        } else {
            //Making new token
            $token = $this->random($this->config->getTokenLength());

            //Token cookie!
            $cookie = $this->tokenCookie($token);
        }

        //CSRF issues must be handled by Firewall middleware
        $response = $handler->handle($request->withAttribute(static::ATTRIBUTE, $token));

        if (!empty($cookie)) {
            return $response->withAddedHeader('Set-Cookie', $cookie);
        }

        return $response;
    }

    /**
     * Generate CSRF cookie.
     *
     * @param string $token
     * @return string
     */
    protected function tokenCookie(string $token): string
    {
        return Cookie::create(
            $this->config->getCookie(),
            $token,
            $this->config->getCookieLifetime(),
            null,
            null,
            $this->config->isCookieSecure(),
            true,
            $this->config->getSameSite()
        )->createHeader();
    }

    /**
     * Create a random string with desired length.
     *
     * @param int $length String length. 32 symbols by default.
     * @return string
     */
    private function random(int $length = 32): string
    {
        try {
            if (empty($string = random_bytes($length))) {
                throw new \RuntimeException('Unable to generate random string');
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to generate random string', $e->getCode(), $e);
        }

        return substr(base64_encode($string), 0, $length);
    }
}

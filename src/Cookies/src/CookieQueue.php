<?php

declare(strict_types=1);

namespace Spiral\Cookies;

use Spiral\Cookies\Middleware\CookiesMiddleware;
use Spiral\Core\Attribute\Scope;

/**
 * @note The CookieQueue might be accessed in the http scope after the {@see CookiesMiddleware} has been executed,
 *       but don't store this class in stateful services, which are not isolated in the http-request scope.
 */
#[Scope('http-request')]
final class CookieQueue
{
    public const ATTRIBUTE = 'cookieQueue';

    /** @var Cookie[] */
    private array $scheduled = [];

    public function __construct(
        private readonly ?string $domain = null,
        private readonly bool $secure = false
    ) {
    }

    /**
     * Schedule new cookie. Cookie will be send while dispatching request.
     *
     * Domain, path, and secure values can be left in null state, in this case cookie manager will
     * populate them automatically.
     *
     * @link http://php.net/manual/en/function.setcookie.php
     *
     * @param string      $name     The name of the cookie.
     * @param string|null $value    The value of the cookie. This value is stored on the clients
     *                              computer; do not store sensitive information.
     * @param int|null    $lifetime Cookie lifetime. This value specified in seconds and declares period
     *                              of time in which cookie will expire relatively to current time()
     *                              value.
     * @param string|null $path     The path on the server in which the cookie will be available on.
     *                              If set to '/', the cookie will be available within the entire
     *                              domain.
     *                              If set to '/foo/', the cookie will only be available within the
     *                              /foo/
     *                              directory and all sub-directories such as /foo/bar/ of domain. The
     *                              default value is the current directory that the cookie is being set
     *                              in.
     * @param string|null $domain   The domain that the cookie is available. To make the cookie
     *                              available
     *                              on all subdomains of example.com then you'd set it to
     *                              '.example.com'.
     *                              The . is not required but makes it compatible with more browsers.
     *                              Setting it to www.example.com will make the cookie only available in
     *                              the www subdomain. Refer to tail matching in the spec for details.
     * @param bool        $secure   Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection from the client. When set to true, the cookie will
     *                              only be set if a secure connection exists. On the server-side, it's
     *                              on the programmer to send this kind of cookie only on secure
     *                              connection (e.g. with respect to $_SERVER["HTTPS"]).
     * @param bool        $httpOnly When true the cookie will be made accessible only through the HTTP
     *                              protocol. This means that the cookie won't be accessible by
     *                              scripting
     *                              languages, such as JavaScript. This setting can effectively help to
     *                              reduce identity theft through XSS attacks (although it is not
     *                              supported by all browsers).
     * @param string|null $sameSite The value of the samesite element should be either None, Lax or Strict. If any of
     *                              the allowed options are not given, their default values are the same as the default
     *                              values of the explicit parameters. If the samesite element is omitted, no SameSite
     *                              cookie attribute is set. When Same-Site attribute is set to "None" it is required
     *                              to have "Secure" attribute enable. Otherwise it will be converted to "Lax".
     */
    public function set(
        string $name,
        string $value = null,
        int $lifetime = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = true,
        ?string $sameSite = null
    ): self {
        if (\is_null($domain)) {
            //Let's resolve domain via config
            $domain = $this->domain;
        }

        if (\is_null($secure)) {
            $secure = $this->secure;
        }

        return $this->schedule(
            new Cookie($name, $value, $lifetime, $path, $domain, $secure, $httpOnly, $sameSite)
        );
    }

    /**
     * Schedule new cookie instance to be send while dispatching request.
     */
    public function schedule(Cookie $cookie): CookieQueue
    {
        $this->scheduled[] = $cookie;

        return $this;
    }

    /**
     * Schedule cookie removal.
     */
    public function delete(string $name): void
    {
        foreach ($this->scheduled as $index => $cookie) {
            if ($cookie->getName() === $name) {
                unset($this->scheduled[$index]);
            }
        }

        $this->scheduled[] = new Cookie($name, null, -86400);
    }

    /**
     * Cookies has to be send (specified via global scope).
     *
     * @return Cookie[]
     */
    public function getScheduled(): array
    {
        return $this->scheduled;
    }
}

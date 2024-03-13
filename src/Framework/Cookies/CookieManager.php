<?php

declare(strict_types=1);

namespace Spiral\Cookies;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Attribute\Scope;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Exception\ScopeException;
use Spiral\Framework\Spiral;

/**
 * Cookies manages provides the ability to write and read cookies from the active request/response scope.
 */
#[Singleton]
#[Scope(Spiral::HttpRequest)]
final class CookieManager
{
    public function __construct(
        #[Proxy] private readonly ContainerInterface $container,
    ) {
    }

    /**
     * @throws ScopeException
     */
    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->getRequest()->getCookieParams());
    }

    /**
     * @throws ScopeException
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->getRequest()->getCookieParams()[$name] ?? $default;
    }

    /**
     * Get all cookies.
     */
    public function getAll(): array
    {
        return $this->getRequest()->getCookieParams();
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
     * @return $this
     *
     */
    public function set(
        string $name,
        ?string $value = null,
        ?int $lifetime = null,
        ?string $path = null,
        ?string $domain = null,
        ?bool $secure = null,
        bool $httpOnly = true,
        ?string $sameSite = null
    ): self {
        $this->getCookieQueue()->set($name, $value, $lifetime, $path, $domain, $secure, $httpOnly, $sameSite);

        return $this;
    }

    /**
     * Schedule new cookie instance to be send while dispatching request.
     *
     * @return $this
     */
    public function schedule(Cookie $cookie): self
    {
        $this->getCookieQueue()->schedule($cookie);

        return $this;
    }

    /**
     * Schedule cookie removal.
     *
     * @throws ScopeException
     */
    public function delete(string $name): void
    {
        $this->getCookieQueue()->delete($name);
    }

    /**
     * Cookies has to be send (specified via global scope).
     *
     * @return Cookie[]
     *
     * @throws ScopeException
     */
    public function getScheduled(): array
    {
        return $this->getCookieQueue()->getScheduled();
    }

    /**
     * @throws ScopeException
     */
    private function getRequest(): ServerRequestInterface
    {
        try {
            return $this->container->get(ServerRequestInterface::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException('Unable to receive active request', $e->getCode(), $e);
        }
    }

    /**
     * @throws ScopeException
     */
    private function getCookieQueue(): CookieQueue
    {
        try {
            return $this->container->get(CookieQueue::class);
        } catch (NotFoundExceptionInterface $e) {
            throw new ScopeException('Unable to receive cookie queue, invalid request scope', $e->getCode(), $e);
        }
    }
}

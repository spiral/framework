<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Cookies;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\ScopeException;

/**
 * Cookies manages provides the ability to write and read cookies from the active request/response scope.
 */
final class CookieManager implements SingletonInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @return bool
     *
     * @throws ScopeException
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->getRequest()->getCookieParams());
    }

    /**
     * @param string $name
     * @param null   $default
     * @return mixed
     *
     * @throws ScopeException
     */
    public function get(string $name, $default = null)
    {
        return $this->getRequest()->getCookieParams()[$name] ?? $default;
    }

    /**
     * Get all cookies.
     *
     * @return array
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
     * @param string $name The name of the cookie.
     * @param string $value The value of the cookie. This value is stored on the clients
     *                         computer; do not store sensitive information.
     * @param int    $lifetime Cookie lifetime. This value specified in seconds and declares period
     *                         of time in which cookie will expire relatively to current time()
     *                         value.
     * @param string $path The path on the server in which the cookie will be available on.
     *                         If set to '/', the cookie will be available within the entire
     *                         domain.
     *                         If set to '/foo/', the cookie will only be available within the
     *                         /foo/
     *                         directory and all sub-directories such as /foo/bar/ of domain. The
     *                         default value is the current directory that the cookie is being set
     *                         in.
     * @param string $domain The domain that the cookie is available. To make the cookie
     *                         available
     *                         on all subdomains of example.com then you'd set it to
     *                         '.example.com'.
     *                         The . is not required but makes it compatible with more browsers.
     *                         Setting it to www.example.com will make the cookie only available in
     *                         the www subdomain. Refer to tail matching in the spec for details.
     * @param bool   $secure Indicates that the cookie should only be transmitted over a secure
     *                         HTTPS connection from the client. When set to true, the cookie will
     *                         only be set if a secure connection exists. On the server-side, it's
     *                         on the programmer to send this kind of cookie only on secure
     *                         connection (e.g. with respect to $_SERVER["HTTPS"]).
     * @param bool   $httpOnly When true the cookie will be made accessible only through the HTTP
     *                         protocol. This means that the cookie won't be accessible by
     *                         scripting
     *                         languages, such as JavaScript. This setting can effectively help to
     *                         reduce identity theft through XSS attacks (although it is not
     *                         supported by all browsers).
     *
     * @return $this
     *
     * @throws ScopeException
     */
    public function set(
        string $name,
        string $value = null,
        int $lifetime = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        bool $httpOnly = true
    ) {
        $this->getCookieQueue()->set($name, $value, $lifetime, $path, $domain, $secure, $httpOnly);

        return $this;
    }

    /**
     * Schedule new cookie instance to be send while dispatching request.
     *
     * @param Cookie $cookie
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
     * @param string $name
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
     * @return ServerRequestInterface
     *
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
     * @return CookieQueue
     *
     * @throws ScopeException
     */
    private function getCookieQueue(): CookieQueue
    {
        $request = $this->getRequest();
        $queue = $request->getAttribute(CookieQueue::ATTRIBUTE, null);
        if ($queue === null) {
            throw new ScopeException('Unable to receive cookie queue, invalid request scope');
        }

        return $queue;
    }
}

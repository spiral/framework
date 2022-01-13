<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Cookies;

/**
 * Represent singular cookie header value with packing abilities.
 */
final class Cookie
{
    /**
     * The name of the cookie.
     *
     * @var string
     */
    private $name;

    /**
     * The value of the cookie. This value is stored on the clients computer; do not store sensitive information.
     *
     * @var string|null
     */
    private $value;

    /**
     * Cookie lifetime. This value specified in seconds and declares period of time in which cookie will expire
     * relatively to current time() value.
     *
     * @var int|null
     */
    private $lifetime;

    /**
     * The path on the server in which the cookie will be available on.
     * If set to '/', the cookie will be available within the entire domain. If set to '/foo/', the cookie will only be
     * available within the /foo/ directory and all sub-directories such as /foo/bar/ of domain. The default value is
     * the current directory that the cookie is being set in.
     *
     * @var string|null
     */
    private $path;

    /**
     * The domain that the cookie is available. To make the cookie available on all subdomains ofexample.com then you'd
     * set it to '.example.com'. The . is not required but makes itcompatible with more browsers. Setting it to
     * www.example.com will make the cookie onlyavailable in the www subdomain. Refer to tail matching in the spec for
     * details.
     *
     * @var string|null
     */
    private $domain;

    /**
     * Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client. When set to
     * true, the cookie will only be set if a secure connection exists. On the server-side, it's on the programmer to
     * send this kind of cookie only on secure connection (e.g. with respect to $_SERVER["HTTPS"]).
     *
     * @var bool
     */
    private $secure;

    /**
     * When true the cookie will be made accessible only through the HTTP protocol. This means that the cookie won't be
     * accessible by scripting languages, such as JavaScript. This setting can effectively help to reduce identity
     * theft through XSS attacks (although it is not supported by all browsers).
     *
     * @var bool
     */
    private $httpOnly;

    /**
     * The value of the samesite element should be either None, Lax or Strict. If any of the allowed options are not
     * given, their default values are the same as the default values of the explicit parameters. If the samesite
     * element is omitted, no SameSite cookie attribute is set. When Same-Site attribute is set to "None" it is
     * required to have "Secure" attribute enable. Otherwise it will be converted to "Lax".
     *
     * @var Cookie\SameSite
     */
    private $sameSite;

    /**
     * New Cookie instance, cookies used to schedule cookie set while dispatching Response.
     *
     * @link http://php.net/manual/en/function.setcookie.php
     *
     * @param string      $name     The name of the cookie.
     * @param string|null $value    The value of the cookie. This value is stored on the clients computer; do not store
     *                              sensitive information.
     * @param int|null    $lifetime Cookie lifetime. This value specified in seconds and declares period of time in
     *                              which cookie will expire relatively to current time() value.
     * @param string|null $path     The path on the server in which the cookie will be available on. If set to '/', the
     *                              cookie will be available within the entire domain. If set to '/foo/', the cookie
     *                              will only be available within the /foo/ directory and all sub-directories such as
     *                              /foo/bar/ of domain. The default value is the current directory that the cookie is
     *                              being set in.
     * @param string|null $domain   The domain that the cookie is available. To make the cookie available on all
     *                              subdomains of example.com then you'd set it to '.example.com'. The . is not
     *                              required but makes it compatible with more browsers. Setting it to www.example.com
     *                              will make the cookie only available in the www subdomain. Refer to tail matching in
     *                              the spec for details.
     * @param bool        $secure   Indicates that the cookie should only be transmitted over a secure HTTPS connection
     *                              from the client. When set to true, the cookie will only be set if a secure
     *                              connection exists. On the server-side, it's on the programmer to send this kind of
     *                              cookie only on secure connection (e.g. with respect to $_SERVER["HTTPS"]).
     * @param bool        $httpOnly When true the cookie will be made accessible only through the HTTP protocol. This
     *                              means that the cookie won't be accessible by scripting languages, such as
     *                              JavaScript. This setting can effectively help to reduce identity theft through XSS
     *                              attacks (although it is not supported by all browsers).
     * @param string|null $sameSite The value of the samesite element should be either None, Lax or Strict. If any of
     *                              the allowed options are not given, their default values are the same as the default
     *                              values of the explicit parameters. If the samesite element is omitted, no SameSite
     *                              cookie attribute is set. When Same-Site attribute is set to "None" it is required
     *                              to have "Secure" attribute enable. Otherwise it will be converted to "Lax".
     */
    public function __construct(
        string $name,
        ?string $value = null,
        ?int $lifetime = null,
        ?string $path = null,
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        ?string $sameSite = null
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->lifetime = $lifetime;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = new Cookie\SameSite($sameSite, $secure);
    }

    public function __toString(): string
    {
        return $this->createHeader();
    }

    /**
     * The name of the cookie.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * The value of the cookie. This value is stored on the clients computer; do not store sensitive information.
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * The path on the server in which the cookie will be available on. If set to '/', the cookie will be available
     * within the entire domain. If set to '/foo/', the cookie will only be available within the /foo/ directory and
     * all sub-directories such as /foo/bar/ of domain. The default value is the current directory that the cookie is
     * being set in.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * The domain that the cookie is available. To make the cookie available on all subdomains of example.com then
     * you'd set it to '.example.com'. The . is not required but makes it compatible with more browsers. Setting it to
     * www.example.com will make the cookie only available in the www subdomain. Refer to tail matching in the spec for
     * details.
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client. When set to
     * true, the cookie will only be set if a secure connection exists. On the server-side, it's on the programmer to
     * send this kind of cookie only on secure connection (e.g. with respect to $_SERVER["HTTPS"]).
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * When true the cookie will be made accessible only through the HTTP protocol. This means that the cookie won't be
     * accessible by scripting languages, such as JavaScript. This setting can effectively help to reduce identity
     * theft through XSS attacks (although it is not supported by all browsers).
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * The value of the samesite element should be either None, Lax or Strict. If any of the allowed options are not
     * given, their default values are the same as the default values of the explicit parameters. If the samesite
     * element is omitted, no SameSite cookie attribute is set. When Same-Site attribute is set to "None" it is
     * required to have "Secure" attribute enable. Otherwise it will be converted to "Lax".
     *
     * @return string
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite->get();
    }

    /**
     * Get new cookie with altered value. Original cookie object should not be changed.
     */
    public function withValue(string $value): self
    {
        $cookie = clone $this;
        $cookie->value = $value;

        return $cookie;
    }

    /**
     * Convert cookie instance to string.
     *
     * @link http://www.w3.org/Protocols/rfc2109/rfc2109
     */
    public function createHeader(): string
    {
        $header = [rawurlencode($this->name) . '=' . rawurlencode((string)$this->value)];

        if ($this->lifetime !== null) {
            $header[] = 'Expires=' . gmdate(\DateTime::COOKIE, $this->getExpires());
            $header[] = "Max-Age={$this->lifetime}";
        }

        if (!empty($this->path)) {
            $header[] = "Path={$this->path}";
        }

        if (!empty($this->domain)) {
            $header[] = "Domain={$this->domain}";
        }

        if ($this->secure) {
            $header[] = 'Secure';
        }

        if ($this->httpOnly) {
            $header[] = 'HttpOnly';
        }

        if ($this->sameSite->get() !== null) {
            $header[] = "SameSite={$this->sameSite->get()}";
        }

        return implode('; ', $header);
    }

    /**
     * The time the cookie expires. This is a Unix timestamp so is in number of seconds since the epoch. In other
     * words, you'll most likely set this with the time function plus the number of seconds before you want it to
     * expire. Or you might use mktime. Will return null if lifetime is not specified.
     */
    public function getExpires(): ?int
    {
        if ($this->lifetime === null) {
            return null;
        }

        return time() + $this->lifetime;
    }

    /**
     * New Cookie instance, cookies used to schedule cookie set while dispatching Response. Static constructor.
     *
     * @link http://php.net/manual/en/function.setcookie.php
     *
     * @param string      $name     The name of the cookie.
     * @param string|null $value    The value of the cookie. This value is stored on the clients computer; do not store
     *                              sensitive information.
     * @param int|null    $lifetime Cookie lifetime. This value specified in seconds and declares period of time in
     *                              which cookie will expire relatively to current time() value.
     * @param string|null $path     The path on the server in which the cookie will be available on. If set to '/', the
     *                              cookie will be available within the entire domain. If set to '/foo/', the cookie
     *                              will only be available within the /foo/ directory and all sub-directories such as
     *                              /foo/bar/ of domain. The default value is the current directory that the cookie is
     *                              being set in.
     * @param string|null $domain   The domain that the cookie is available. To make the cookie available on all
     *                              subdomains of example.com then you'd set it to '.example.com'. The . is not
     *                              required but makes it compatible with more browsers. Setting it to www.example.com
     *                              will make the cookie only available in the www subdomain. Refer to tail matching in
     *                              the spec for details.
     * @param bool        $secure   Indicates that the cookie should only be transmitted over a secure HTTPS connection
     *                              from the client. When set to true, the cookie will only be set if a secure
     *                              connection exists. On the server-side, it's on the programmer to send this kind of
     *                              cookie only on secure connection (e.g. with respect to $_SERVER["HTTPS"]).
     * @param bool        $httpOnly When true the cookie will be made accessible only through the HTTP protocol. This
     *                              means that the cookie won't be accessible by scripting languages, such as
     *                              JavaScript. This setting can effectively help to reduce identity theft through XSS
     *                              attacks (although it is not supported by all browsers).
     * @param string|null $sameSite The value of the samesite element should be either None, Lax or Strict. If any of
     *                              the allowed options are not given, their default values are the same as the default
     *                              values of the explicit parameters. If the samesite element is omitted, no SameSite
     *                              cookie attribute is set. When Same-Site attribute is set to "None" it is required
     *                              to have "Secure" attribute enable. Otherwise it will be converted to "Lax".
     */
    public static function create(
        string $name,
        ?string $value = null,
        ?int $lifetime = null,
        ?string $path = null,
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        ?string $sameSite = null
    ): self {
        return new self($name, $value, $lifetime, $path, $domain, $secure, $httpOnly, $sameSite);
    }
}

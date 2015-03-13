<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Cookies;

interface CookieInterface
{
    /**
     * The name of the cookie.
     *
     * @return string
     */
    public function getName();

    /**
     * The value of the cookie. This value is stored on the clients computer; do not store sensitive
     * information.
     *
     * @return string
     */
    public function getValue();

    /**
     * The time the cookie expires. This is a Unix timestamp so is in number of seconds since the epoch.
     * In other words, you'll most likely set this with the time function plus the number of seconds
     * before you want it to expire. Or you might use mktime.
     *
     * @return int
     */
    public function getExpire();

    /**
     * The path on the server in which the cookie will be available on.
     *
     * If set to '/', the cookie will be available within the entire domain. If set to '/foo/',
     * the cookie will only be available within the /foo/ directory and all sub-directories such as
     * /foo/bar/ of domain. The default value is the current directory that the cookie is being set in.
     *
     * @return string
     */
    public function getPath();

    /**
     * The domain that the cookie is available. To make the cookie available on all subdomains of
     * example.com then you'd set it to '.example.com'. The . is not required but makes it compatible
     * with more browsers. Setting it to www.example.com will make the cookie only available in the www
     * subdomain. Refer to tail matching in the spec for details.
     *
     * @return string|null
     */
    public function getDomain();

    /**
     * Indicates that the cookie should only be transmitted over a secure HTTPS connection from the
     * client. When set to true, the cookie will only be set if a secure connection exists.
     * On the server-side, it's on the programmer to send this kind of cookie only on secure connection
     * (e.g. with respect to $_SERVER["HTTPS"]).
     *
     * @return bool
     */
    public function getSecure();

    /**
     * When true the cookie will be made accessible only through the HTTP protocol. This means that
     * the cookie won't be accessible by scripting languages, such as JavaScript. This setting can
     * effectively help to reduce identity theft through XSS attacks (although it is not supported
     * by all browsers).
     *
     * @return bool
     */
    public function getHttpOnly();
}
<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Cookies;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\Configs\HttpConfig;

class CookieQueue
{
    /**
     * Cookies has to be send (specified via global scope).
     *
     * @var Cookie[]
     */
    private $scheduled = [];

    /**
     * @invisible
     * @var HttpConfig
     */
    private $httpConfig = null;

    /**
     * Associated request (to fetch domain name from).
     *
     * @invisible
     * @var ServerRequestInterface
     */
    private $request = null;

    /**
     * @param HttpConfig             $httpConfig
     * @param ServerRequestInterface $request
     */
    public function __construct(HttpConfig $httpConfig, ServerRequestInterface $request)
    {
        $this->httpConfig = $httpConfig;
        $this->request = $request;
    }

    /**
     * Schedule new cookie. Cookie will be send while dispatching request.
     *
     * Domain, path, and secure values can be left in null state, in this case cookie manager will
     * populate them automatically.
     *
     * @link http://php.net/manual/en/function.setcookie.php
     * @param string $name     The name of the cookie.
     * @param string $value    The value of the cookie. This value is stored on the clients
     *                         computer; do not store sensitive information.
     * @param int    $lifetime Cookie lifetime. This value specified in seconds and declares period
     *                         of time in which cookie will expire relatively to current time()
     *                         value.
     * @param string $path     The path on the server in which the cookie will be available on.
     *                         If set to '/', the cookie will be available within the entire
     *                         domain.
     *                         If set to '/foo/', the cookie will only be available within the
     *                         /foo/
     *                         directory and all sub-directories such as /foo/bar/ of domain. The
     *                         default value is the current directory that the cookie is being set
     *                         in.
     * @param string $domain   The domain that the cookie is available. To make the cookie
     *                         available
     *                         on all subdomains of example.com then you'd set it to
     *                         '.example.com'.
     *                         The . is not required but makes it compatible with more browsers.
     *                         Setting it to www.example.com will make the cookie only available in
     *                         the www subdomain. Refer to tail matching in the spec for details.
     * @param bool   $secure   Indicates that the cookie should only be transmitted over a secure
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
     * @return $this
     */
    public function set(
        $name,
        $value = null,
        $lifetime = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httpOnly = true
    ) {
        if (is_null($domain)) {
            $domain = $this->httpConfig->cookiesDomain($this->request->getUri());
        }

        if (is_null($secure)) {
            $secure = $this->request->getMethod() == 'https';
        }

        return $this->schedule(
            new Cookie($name, $value, $lifetime, $path, $domain, $secure, $httpOnly)
        );
    }

    /**
     * Schedule new cookie instance to be send while dispatching request.
     *
     * @param Cookie $cookie
     * @return $this
     */
    public function schedule(Cookie $cookie)
    {
        $this->scheduled[] = $cookie;

        return $this;
    }

    /**
     * Schedule cookie removal.
     *
     * @param string $name
     */
    public function delete($name)
    {
        foreach ($this->scheduled as $index => $cookie) {
            if ($cookie->getName() == $name) {
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
    public function getScheduled()
    {
        return $this->scheduled;
    }
}
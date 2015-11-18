<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Cookies;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Core\Traits\ConfigurableTrait;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\Exceptions\DecryptException;
use Spiral\Encrypter\Exceptions\EncrypterException;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\MiddlewareInterface;
use Spiral\Http\Middlewares\CsrfFilter;
use Spiral\Session\Http\SessionStarter;

/**
 * Middleware used to encrypt and decrypt cookies. In addition it will set cookieDomain request
 * attribute which can be used by other middlewares.
 *
 * Even if cookie manager can be used in general part of application as singleton - it is not.
 * Shared binding will be available only while CookieManager performing incoming request, as result
 * you can use CookieManager instance on controller level.
 */
class CookieManager extends Component implements MiddlewareInterface
{
    /**
     * Can be configured.
     */
    use ConfigurableTrait;

    /**
     * Cookie protection modes.
     */
    const NONE    = 'none';
    const ENCRYPT = 'encrypt';
    const HMAC    = 'hmac';

    /**
     * Algorithm used to sign cookies.
     */
    const HMAC_ALGORITHM = 'sha256';

    /**
     * Generated MAC length, has to be stripped from cookie.
     */
    const MAC_LENGTH = 64;

    /**
     * Cookie names should never be encrypted or decrypted.
     *
     * @var array
     */
    private $exclude = [CsrfFilter::COOKIE, SessionStarter::COOKIE];

    /**
     * Cookies has to be send (specified via global scope).
     *
     * @var Cookie[]
     */
    private $scheduled = [];

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @invisible
     * @var ServerRequestInterface
     */
    protected $request = null;

    /**
     * @invisible
     * @var EncrypterInterface
     */
    protected $encrypter = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(HttpConfig $config, ContainerInterface $container)
    {
        //Yes, by default we are using configuration of parent HttpDispatcher (i probably need a
        //method for that).
        $this->config = $config;
        $this->container = $container;
    }

    /**
     * Disable encryption/decryption for specified cookie.
     *
     * @param string $name
     * @return $this
     */
    public function excludeCookie($name)
    {
        $this->exclude[] = $name;

        return $this;
    }

    /**
     * @param EncrypterInterface $encrypter
     */
    public function setEncrypter(EncrypterInterface $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        //Opening cookie scope
        $outerCookies = $this->container->replace(self::class, $this);
        $this->request = $this->decodeCookies($request);

        $response = $next(
            $this->request->withAttribute('cookieDomain', $this->cookieDomain())
        );

        //New cookies
        $response = $this->mountCookies($response);

        //Restoring scope
        $this->container->restore($outerCookies);

        return $response;
    }

    /**
     * Create new cookie instance without adding it to scheduled list.
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
     * @return Cookie
     */
    public function create(
        $name,
        $value = null,
        $lifetime = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httpOnly = true
    ) {
        if (is_null($domain)) {
            $domain = $this->cookieDomain();
        }

        if (is_null($secure)) {
            $secure = $this->request->getMethod() == 'https';
        }

        return new Cookie($name, $value, $lifetime, $path, $domain, $secure, $httpOnly);
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
     * @return Cookie
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
        $cookie = $this->create($name, $value, $lifetime, $path, $domain, $secure, $httpOnly);
        $this->scheduled[] = $cookie;

        return $cookie;
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
     * Schedule new cookie instance to be send while dispatching request.
     *
     * @param Cookie $cookie
     * @return $this
     */
    public function add(Cookie $cookie)
    {
        $this->scheduled[] = $cookie;

        return $this;
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

    /**
     * Get associated encrypter instance or fetch it from container.
     *
     * @return EncrypterInterface
     */
    protected function encrypter()
    {
        if (!empty($this->encrypter)) {
            return $this->encrypter;
        }

        return $this->encrypter = $this->container->get(EncrypterInterface::class);
    }

    /**
     * Default domain to set cookies for. Domain pattern specified in cookie config is presented as
     * valid sprintf expression.
     *
     * Example:
     * mydomain.com //Forced domain value
     * %s           //Cookies will be mounted on current domain
     * .%s          //Cookies will be mounted on current domain and sub domains
     *
     * @return string
     */
    protected function cookieDomain()
    {
        $host = $this->request->getUri()->getHost();

        $pattern = $this->config['domain'];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            //We can't use sub domains
            $pattern = ltrim($pattern, '.');
        }

        if (!empty($port = $this->request->getUri()->getPort())) {
            $host = $host . ':' . $port;
        }

        if (strpos($pattern, '%s') === false) {
            //Forced domain
            return $pattern;
        }

        return sprintf($pattern, $host);
    }

    /**
     * Unpack incoming cookies and decrypt their content.
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function decodeCookies(ServerRequestInterface $request)
    {
        $altered = false;
        $cookies = $request->getCookieParams();

        foreach ($cookies as $name => $cookie) {
            if (in_array($name, $this->exclude) || $this->config['method'] == self::NONE) {
                continue;
            }

            $altered = true;
            $cookies[$name] = $this->decodeCookie($cookie);
        }

        return $altered ? $request->withCookieParams($cookies) : $request;
    }

    /**
     * @param string|array $cookie
     * @return array|mixed|null
     */
    private function decodeCookie($cookie)
    {
        if ($this->config['method'] == self::ENCRYPT) {
            try {
                if (is_array($cookie)) {
                    return array_map([$this, 'decodeCookie'], $cookie);
                }

                return $this->encrypter()->decrypt($cookie);
            } catch (DecryptException $exception) {
                return null;
            }
        }

        //HMAC
        $hmac = substr($cookie, -1 * self::MAC_LENGTH);
        $value = substr($cookie, 0, strlen($cookie) - strlen($hmac));

        if ($this->hmacSign($value) != $hmac) {
            return null;
        }

        return $value;
    }

    /**
     * Pack outcoming cookies with encrypted value.
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws EncrypterException
     */
    protected function mountCookies(ResponseInterface $response)
    {
        if (empty($this->scheduled)) {
            return $response;
        }

        $cookies = $response->getHeader('Set-Cookie');

        //Merging cookies
        foreach ($this->scheduled as $cookie) {
            if (
                in_array($cookie->getName(), $this->exclude)
                || $this->config['method'] == self::NONE
            ) {
                $cookies[] = $cookie->packHeader();
                continue;
            }

            $cookies[] = $this->encodeCookie($cookie)->packHeader();
        }

        $this->scheduled = [];

        return $response->withHeader('Set-Cookie', $cookies);
    }

    /**
     * @param Cookie $cookie
     * @return Cookie
     */
    private function encodeCookie(Cookie $cookie)
    {
        if ($this->config['method'] == self::ENCRYPT) {
            return $cookie->withValue(
                $this->encrypter()->encrypt($cookie->getValue())
            );
        }

        //MAC
        return $cookie->withValue($cookie->getValue() . $this->hmacSign($cookie->getValue()));
    }

    /**
     * Sign string.
     *
     * @param string $value
     * @return string
     */
    private function hmacSign($value)
    {
        return hash_hmac(self::HMAC_ALGORITHM, $value, $this->encrypter()->getKey());
    }
}

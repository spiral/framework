<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Cookies;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Core\Component;
use Spiral\Core\ContainerInterface;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\Exceptions\DecryptException;
use Spiral\Encrypter\Exceptions\EncrypterException;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\MiddlewareInterface;

/**
 * Middleware used to encrypt and decrypt cookies.
 *
 * Even if cookie manager can be used in general part of application as singleton - it is not.
 * Shared binding will be available only while CookieManager performing incoming request, as result
 * you can use CookieManager instance on controller level.
 *
 * Attention, EncrypterInterface is requested from container on demand.
 */
class CookieManager extends Component implements MiddlewareInterface
{
    /**
     * Needed to validly set cookie domain.
     *
     * @invisible
     * @var Request
     */
    private $request = null;

    /**
     * Cookies has to be send (specified via global scope).
     *
     * @var Cookie[]
     */
    private $scheduled = [];

    /**
     * Cookie names to be excluded from protection.
     *
     * @var array
     */
    private $excluded = [];

    /**
     * @var EncrypterInterface
     */
    protected $encrypter = null;

    /**
     * @var HttpConfig
     */
    protected $config = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param HttpConfig         $config
     * @param ContainerInterface $container
     */
    public function __construct(HttpConfig $config, ContainerInterface $container)
    {
        $this->config = $config;
        $this->excluded = $config->excludedCookies();
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
        $this->excluded[] = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        //Opening cookie scope
        $scope = $this->container->replace(self::class, $this);

        try {
            //Needed to resolve cookie domain
            $this->request = $request;

            /**
             * Debug: middleware creates scope for CookieManager.
             */
            $response = $next($this->unpackCookies($request), $response);

            //New cookies
            return $this->packCookies($response);
        } finally {
            $this->scheduled = [];
            $this->request = null;
            $this->container->restore($scope);
        }
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
            $domain = $this->config->cookiesDomain($this->request->getUri());
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

    /**
     * Unpack incoming cookies and decrypt their content.
     *
     * @param Request $request
     * @return Request
     */
    protected function unpackCookies(Request $request)
    {
        $cookies = $request->getCookieParams();

        foreach ($cookies as $name => $cookie) {
            if (!$this->isProtected($name)) {
                //Nothing to protect
                continue;
            }

            $cookies[$name] = $this->decodeCookie($cookie);
        }

        return $request->withCookieParams($cookies);
    }

    /**
     * Pack outcoming cookies with encrypted value.
     *
     * @param Response $response
     * @return Response
     * @throws EncrypterException
     */
    protected function packCookies(Response $response)
    {
        if (empty($this->scheduled)) {
            return $response;
        }

        $cookies = $response->getHeader('Set-Cookie');

        foreach ($this->scheduled as $cookie) {
            if (!$this->isProtected($cookie->getName())) {
                $cookies[] = $cookie->packHeader();
                continue;
            }

            $cookies[] = $this->encodeCookie($cookie)->packHeader();
        }

        return $response->withHeader('Set-Cookie', $cookies);
    }

    /**
     * Check if cookie has to be protected.
     *
     * @param string $cookie
     * @return bool
     */
    protected function isProtected($cookie)
    {
        if (in_array($cookie, $this->excluded)) {
            //Excluded
            return false;
        }

        return $this->config->cookieProtection() != HttpConfig::COOKIE_UNPROTECTED;
    }

    /**
     * Get or create encrypter instance.
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
     * @param string|array $cookie
     * @return array|mixed|null
     */
    private function decodeCookie($cookie)
    {
        if ($this->config->cookieProtection() == HttpConfig::COOKIE_ENCRYPT) {
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
        $hmac = substr($cookie, -1 * HttpConfig::MAC_LENGTH);
        $value = substr($cookie, 0, strlen($cookie) - strlen($hmac));

        if ($this->hmacSign($value) != $hmac) {
            return null;
        }

        return $value;
    }

    /**
     * @param Cookie $cookie
     * @return Cookie
     */
    private function encodeCookie(Cookie $cookie)
    {
        if ($this->config->cookieProtection() == HttpConfig::COOKIE_ENCRYPT) {
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
        return hash_hmac(HttpConfig::HMAC_ALGORITHM, $value, $this->encrypter()->getKey());
    }
}

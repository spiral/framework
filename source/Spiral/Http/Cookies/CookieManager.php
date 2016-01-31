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
 * Middleware used to encrypt and decrypt cookies. Creates container scope for a cookie bucket.
 *
 * Attention, EncrypterInterface is requested from container on demand.
 *
 * @todo add simple interface and replace in short bindings
 * @todo split into CookieManager and CookiesBucket (queue)
 */
class CookieManager extends Component implements MiddlewareInterface
{
    /**
     * @var EncrypterInterface
     */
    private $encrypter = null;

    /**
     * @var HttpConfig
     */
    private $httpConfig = null;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param HttpConfig         $httpConfig
     * @param ContainerInterface $container
     */
    public function __construct(HttpConfig $httpConfig, ContainerInterface $container)
    {
        $this->httpConfig = $httpConfig;
        $this->container = $container;
    }

    /**
     * Set custom instance of encrypter (by default resolved from container on demand).
     *
     * @param EncrypterInterface $encrypter
     * @return $this
     */
    public function setEncrypter(EncrypterInterface $encrypter)
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        $queue = $this->container->make(CookieQueue::class, [
            'request'    => $request,
            'httpConfig' => $this->httpConfig
        ]);

        //Opening cookie scope
        $scope = $this->container->replace(CookieQueue::class, $queue);

        try {
            /**
             * Debug: middleware creates scope for [CookieQueue].
             */
            $response = $next(
                $this->unpackCookies($request),
                $response
            );

            //New cookies
            return $this->packCookies($response, $queue);
        } finally {
            $this->container->restore($scope);
        }
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
     * @param Response    $response
     * @param CookieQueue $queue
     * @return Response
     * @throws EncrypterException
     */
    protected function packCookies(Response $response, CookieQueue $queue)
    {
        if (empty($queue->getScheduled())) {
            return $response;
        }

        $cookies = $response->getHeader('Set-Cookie');

        foreach ($queue->getScheduled() as $cookie) {
            if (!$this->isProtected($cookie->getName())) {
                $cookies[] = $cookie->createHeader();
                continue;
            }

            $cookies[] = $this->encodeCookie($cookie)->createHeader();
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
        if (in_array($cookie, $this->httpConfig->excludedCookies())) {
            //Excluded
            return false;
        }

        return $this->httpConfig->cookieProtection() != HttpConfig::COOKIE_UNPROTECTED;
    }

    /**
     * Get or create encrypter instance.
     *
     * @return EncrypterInterface
     */
    protected function encrypter()
    {
        if (empty($this->encrypter)) {
            $this->encrypter = $this->container->get(EncrypterInterface::class);
        }

        return $this->encrypter;
    }

    /**
     * @param string|array $cookie
     * @return array|mixed|null
     */
    private function decodeCookie($cookie)
    {
        if ($this->httpConfig->cookieProtection() == HttpConfig::COOKIE_ENCRYPT) {
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
        if ($this->httpConfig->cookieProtection() == HttpConfig::COOKIE_ENCRYPT) {
            return $cookie->withValue(
                $this->encrypter()->encrypt($cookie->getValue())
            );
        }

        //VALUE.HMAC
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

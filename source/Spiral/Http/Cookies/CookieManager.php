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
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\MiddlewareInterface;

/**
 * Middleware used to encrypt and decrypt cookies. Creates container scope for a cookie bucket.
 *
 * Attention, EncrypterInterface is requested from container on demand.
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
     *
     * @return $this|self
     */
    public function setEncrypter(EncrypterInterface $encrypter): CookieManager
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    /**
     * Get or create encrypter instance.
     *
     * @return EncrypterInterface
     */
    public function getEncrypter()
    {
        if (empty($this->encrypter)) {
            //On demand creation (speed up app when no cookies were set)
            $this->encrypter = $this->container->get(EncrypterInterface::class);
        }

        return $this->encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response, callable $next = null)
    {
        //Aggregates all user cookies
        $queue = new CookieQueue($this->httpConfig, $request);

        //Opening cookie scope
        $scope = $this->container->replace(CookieQueue::class, $queue);
        try {
            /**
             * Debug: middleware creates scope for [CookieQueue].
             */
            $response = $next(
                $this->unpackCookies($request)->withAttribute('cookieQueue', $queue),
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
     *
     * @return Request
     */
    protected function unpackCookies(Request $request): Request
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
     *
     * @return Response
     *
     * @throws \Spiral\Encrypter\Exceptions\EncryptException
     */
    protected function packCookies(Response $response, CookieQueue $queue): Response
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
     *
     * @return bool
     */
    protected function isProtected(string $cookie): bool
    {
        if (in_array($cookie, $this->httpConfig->excludedCookies())) {
            //Excluded
            return false;
        }

        return $this->httpConfig->cookieProtection() != HttpConfig::COOKIE_UNPROTECTED;
    }

    /**
     * @param string|array $cookie
     *
     * @return array|mixed|null
     */
    private function decodeCookie($cookie)
    {
        if ($this->httpConfig->cookieProtection() == HttpConfig::COOKIE_ENCRYPT) {
            try {
                if (is_array($cookie)) {
                    return array_map([$this, 'decodeCookie'], $cookie);
                }

                return $this->getEncrypter()->decrypt($cookie);
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
     *
     * @return Cookie
     */
    private function encodeCookie(Cookie $cookie): Cookie
    {
        if ($this->httpConfig->cookieProtection() == HttpConfig::COOKIE_ENCRYPT) {
            return $cookie->withValue(
                $this->getEncrypter()->encrypt($cookie->getValue())
            );
        }

        //VALUE.HMAC
        return $cookie->withValue($cookie->getValue() . $this->hmacSign($cookie->getValue()));
    }

    /**
     * Sign string.
     *
     * @param string|null $value
     *
     * @return string
     */
    private function hmacSign($value): string
    {
        return hash_hmac(HttpConfig::HMAC_ALGORITHM, $value, $this->getEncrypter()->getKey());
    }
}

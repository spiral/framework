<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Cookies\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Cookies\Config\CookiesConfig;
use Spiral\Cookies\Cookie;
use Spiral\Cookies\CookieQueue;
use Spiral\Encrypter\EncryptionInterface;
use Spiral\Encrypter\Exception\DecryptException;
use Spiral\Encrypter\Exception\EncryptException;

/**
 * Middleware used to encrypt and decrypt cookies. Creates container scope for a cookie bucket.
 *
 * Attention, EncrypterInterface is requested from container on demand.
 */
final class CookiesMiddleware implements MiddlewareInterface
{
    /** @var CookiesConfig */
    private $config;

    /** @var EncryptionInterface */
    private $encryption;

    /**
     * @param CookiesConfig       $config
     * @param EncryptionInterface $encryption
     */
    public function __construct(CookiesConfig $config, EncryptionInterface $encryption)
    {
        $this->config = $config;
        $this->encryption = $encryption;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        //Aggregates all user cookies
        $queue = new CookieQueue(
            $this->config->resolveDomain($request->getUri()),
            $request->getUri()->getScheme() === 'https'
        );

        $response = $handler->handle(
            $this->unpackCookies($request)->withAttribute(CookieQueue::ATTRIBUTE, $queue)
        );

        return $this->packCookies($response, $queue);
    }

    /**
     * Unpack incoming cookies and decrypt their content.
     *
     * @param Request $request
     * @return Request
     */
    protected function unpackCookies(Request $request): Request
    {
        $cookies = $request->getCookieParams();

        foreach ($cookies as $name => $cookie) {
            if (!$this->isProtected($name)) {
                continue;
            }

            $cookies[$name] = $this->decodeCookie($cookie);
        }

        return $request->withCookieParams($cookies);
    }

    /**
     * Check if cookie has to be protected.
     *
     * @param string $cookie
     * @return bool
     */
    protected function isProtected(string $cookie): bool
    {
        if (in_array($cookie, $this->config->getExcludedCookies(), true)) {
            //Excluded
            return false;
        }

        return $this->config->getProtectionMethod() !== CookiesConfig::COOKIE_UNPROTECTED;
    }

    /**
     * Pack outcoming cookies with encrypted value.
     *
     * @param Response    $response
     * @param CookieQueue $queue
     * @return Response
     *
     * @throws EncryptException
     */
    protected function packCookies(Response $response, CookieQueue $queue): Response
    {
        if (empty($queue->getScheduled())) {
            return $response;
        }

        $cookies = $response->getHeader('Set-Cookie');

        foreach ($queue->getScheduled() as $cookie) {
            if (empty($cookie->getValue()) || !$this->isProtected($cookie->getName())) {
                $cookies[] = $cookie->createHeader();
                continue;
            }

            $cookies[] = $this->encodeCookie($cookie)->createHeader();
        }

        return $response->withHeader('Set-Cookie', $cookies);
    }

    /**
     * @param string|array $cookie
     * @return array|mixed|null
     */
    private function decodeCookie($cookie)
    {
        try {
            if (is_array($cookie)) {
                return array_map([$this, 'decodeCookie'], $cookie);
            }
        } catch (DecryptException $exception) {
            return null;
        }

        switch ($this->config->getProtectionMethod()) {
            case CookiesConfig::COOKIE_ENCRYPT:
                try {
                    return $this->encryption->getEncrypter()->decrypt($cookie);
                } catch (DecryptException $e) {
                }
                return null;
            case CookiesConfig::COOKIE_HMAC:
                $hmac = substr($cookie, -1 * CookiesConfig::MAC_LENGTH);
                $value = substr($cookie, 0, strlen($cookie) - strlen($hmac));

                if (hash_equals($this->hmacSign($value), $hmac)) {
                    return $value;
                }
        }

        return null;
    }

    /**
     * Sign string.
     *
     * @param string|null $value
     * @return string
     */
    private function hmacSign($value): string
    {
        return hash_hmac(
            CookiesConfig::HMAC_ALGORITHM,
            $value,
            $this->encryption->getKey()
        );
    }

    /**
     * @param Cookie $cookie
     * @return Cookie
     */
    private function encodeCookie(Cookie $cookie): Cookie
    {
        if ($this->config->getProtectionMethod() === CookiesConfig::COOKIE_ENCRYPT) {
            $encryptor = $this->encryption->getEncrypter();

            return $cookie->withValue($encryptor->encrypt($cookie->getValue()));
        }

        //VALUE.HMAC
        return $cookie->withValue($cookie->getValue() . $this->hmacSign($cookie->getValue()));
    }
}

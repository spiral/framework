<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Configs;

use Psr\Http\Message\UriInterface;
use Spiral\Core\InjectableConfig;
use Spiral\Http\Routing\Router;

/**
 * HttpDispatcher configuration.
 */
class HttpConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'http';

    /**
     * Cookie protection methods.
     */
    const COOKIE_UNPROTECTED = 0;
    const COOKIE_ENCRYPT     = 1;
    const COOKIE_HMAC        = 2;

    /**
     * Algorithm used to sign cookies.
     */
    const HMAC_ALGORITHM = 'sha256';

    /**
     * Generated MAC length, has to be stripped from cookie.
     */
    const MAC_LENGTH = 64;

    /**
     * @var array
     */
    protected $config = [
        'basePath'     => '/',
        'exposeErrors' => false,
        'cookies'      => [
            'domain'   => '.%s',
            'method'   => self::COOKIE_ENCRYPT,
            'excluded' => []
        ],
        'csrf'         => [
            'cookie'   => 'csrf-token',
            'length'   => 16,
            'lifetime' => 86400
        ],
        'headers'      => [],
        'middlewares'  => [],
        'endpoint'     => null,
        'router'       => [
            'class'      => Router::class,
            'parameters' => []
        ],
        'httpErrors'   => []
    ];

    /**
     * @return string
     */
    public function basePath()
    {
        return $this->config['basePath'];
    }

    /**
     * @return bool
     */
    public function exposeErrors()
    {
        return $this->config['exposeErrors'];
    }

    /**
     * Initial set of headers.
     *
     * @return array
     */
    public function defaultHeaders()
    {
        return $this->config['headers'];
    }

    /**
     * Initial middlewares set.
     *
     * @return array
     */
    public function defaultMiddlewares()
    {
        return $this->config['middlewares'];
    }

    /**
     * Default Http endpoint.
     *
     * @return string|null
     */
    public function defaultEndpoint()
    {
        return !empty($this->config['endpoint']) ? $this->config['endpoint'] : null;
    }

    /**
     * Router class to be used if any.
     *
     * @return string
     */
    public function routerClass()
    {
        return $this->config['router']['class'];
    }

    /**
     * Parameters associated with router class.
     *
     * @return array
     */
    public function routerParameters()
    {
        //Let's automatically add basePath value
        return $this->config['router']['parameters'] + ['basePath' => $this->basePath()];
    }

    /**
     * @param string $errorCode
     * @return bool
     */
    public function hasView($errorCode)
    {
        return isset($this->config['httpErrors'][$errorCode]);
    }

    /**
     * @param string $errorCode
     * @return string
     */
    public function errorView($errorCode)
    {
        return $this->config['httpErrors'][$errorCode];
    }

    /**
     * Return config and uri specific cookie domain.
     *
     * @param UriInterface $uri
     * @return string
     */
    public function cookiesDomain(UriInterface $uri)
    {
        $host = $uri->getHost();

        $pattern = $this->config['cookies']['domain'];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            //We can't use sub domains
            $pattern = ltrim($pattern, '.');
        }

        if (!empty($port = $uri->getPort())) {
            $host = $host . ':' . $port;
        }

        if (strpos($pattern, '%s') === false) {
            //Forced domain
            return $pattern;
        }

        return sprintf($pattern, $host);
    }

    /**
     * Cookie protection method.
     *
     * @return int
     */
    public function cookieProtection()
    {
        return $this->config['cookies']['method'];
    }

    /**
     * Cookies to be excluded from protection.
     *
     * @return array
     */
    public function excludedCookies()
    {
        if (empty($this->config['cookies']['excluded'])) {
            return [];
        }

        return $this->config['cookies']['excluded'];
    }

    /**
     * @return string
     */
    public function csrfCookie()
    {
        return $this->config['csrf']['cookie'];
    }

    /**
     * @return int
     */
    public function csrfLength()
    {
        return $this->config['csrf']['length'];
    }

    /**
     * @return int
     */
    public function csrfLifetime()
    {
        return $this->config['csrf']['lifetime'];
    }
}
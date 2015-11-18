<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Configs;

use Spiral\Core\ArrayConfig;
use Spiral\Http\Routing\Router;

/**
 * HttpDispatcher configuration.
 */
class HttpConfig extends ArrayConfig
{
    /**
     * HttpConfig can be used by multiple classes including cookie middlewares, this should speed
     * up it's loading a little bit.
     */
    const SINGLETON = self::class;

    /**
     * Configuration section.
     */
    const CONFIG = 'http';

    /**
     * @var array
     */
    protected $config = [
        'basePath'     => '/',
        'exposeErrors' => true,
        'cookies'      => [
            'domain' => '.%s',
            'method' => 'encrypt',
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
}
<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */
namespace Spiral\Session\Configs;

use Spiral\Core\InjectableConfig;

/**
 * Translation component configuration.
 */
class SessionConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'session';

    /**
     * Default session handler.
     */
    const NATIVE_HANDLER = false;

    /**
     * @var array
     */
    protected $config = [
        'lifetime' => 86400,
        'cookie'   => 'spiral-session',
        'handler'  => self::NATIVE_HANDLER,
        'handlers' => []
    ];

    /**
     * @return int
     */
    public function sessionLifetime()
    {
        return $this->config['lifetime'];
    }

    /**
     * @return string
     */
    public function sessionCookie()
    {
        return $this->config['cookie'];
    }

    /**
     * Default session handler.
     *
     * @return string|mixed
     */
    public function sessionHandler()
    {
        return $this->config['handler'];
    }

    /**
     * @return string
     */
    public function handlerClass()
    {
        return $this->config['handlers'][$this->sessionHandler()]['class'];

    }

    /**
     * @return array
     */
    public function handlerParameters()
    {
        return $this->config['handlers'][$this->sessionHandler()] + ['lifetime' => $this->sessionLifetime()];
    }
}
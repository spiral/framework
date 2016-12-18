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
 * SessionManager configuration.
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
    public function sessionLifetime(): int
    {
        return $this->config['lifetime'];
    }

    /**
     * @return string
     */
    public function sessionCookie(): string
    {
        return $this->config['cookie'];
    }

    /**
     * Default session handler.
     *
     * @return string
     */
    public function defaultHandler(): string
    {
        return $this->config['handler'];
    }

    /**
     * @param string $handler
     *
     * @return string
     */
    public function handlerClass(string $handler): string
    {
        return $this->config['handlers'][$this->defaultHandler()]['class'];
    }

    /**
     * @param string $handler
     *
     * @return array
     */
    public function handlerOptions(string $handler): array
    {
        return $this->config['handlers'][$handler] + ['lifetime' => $this->sessionLifetime()];
    }
}
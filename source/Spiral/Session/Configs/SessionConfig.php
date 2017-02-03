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
     * @var array
     */
    protected $config = [
        'lifetime' => 86400,
        'cookie'   => 'SID',
        'secure'   => false,
        'handler'  => null,
        'handlers' => []
    ];

    /**
     * List of headers to be used for session signature.
     *
     * @return array
     */
    public function signHeaders(): array
    {
        return [
            'User-Agent',
            'Accept-Language'
        ];
    }

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
     * @return bool
     */
    public function sessionSecure(): bool
    {
        return $this->config['secure'] ?? false;
    }

    /**
     * Default session handler. When NULL no handlers to be used.
     *
     * @return string|null
     */
    public function sessionHandler()
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
        return $this->config['handlers'][$handler]['class'];
    }

    /**
     * @param string $handler
     *
     * @return array
     */
    public function handlerOptions(string $handler): array
    {
        return $this->config['handlers'][$handler]['options'] + ['lifetime' => $this->sessionLifetime()];
    }
}
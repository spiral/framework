<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Session\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;

/**
 * SessionManager configuration.
 */
final class SessionConfig extends InjectableConfig
{
    public const CONFIG = 'session';

    /**
     * @var array
     */
    protected $config = [
        'lifetime' => 86400,
        'cookie'   => 'SID',
        'secure'   => false,
        'sameSite' => null,
        'handler'  => null,
        'handlers' => [],
    ];

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->config['lifetime'];
    }

    /**
     * @return string
     */
    public function getCookie(): string
    {
        return $this->config['cookie'];
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->config['secure'] ?? false;
    }

    /**
     * Get handler autowire options.
     *
     * @return Autowire|null
     */
    public function getHandler(): ?Autowire
    {
        if (empty($this->config['handler'])) {
            return null;
        }

        if ($this->config['handler'] instanceof Autowire) {
            return $this->config['handler'];
        }

        if (class_exists($this->config['handler'])) {
            return new Autowire($this->config['handler']);
        }

        $handler = $this->config['handlers'][$this->config['handler']];

        return new Autowire($handler['class'], $handler['options']);
    }

    /**
     * @return string|null
     */
    public function getSameSite(): ?string
    {
        return $this->config['sameSite'] ?? null;
    }
}

<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Auth\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;

/**
 * Manages auth http transport configuration.
 */
final class AuthConfig extends InjectableConfig
{
    // Configuration source.
    public const CONFIG = 'auth';

    /**
     * @return string
     */
    public function getDefaultTransport(): string
    {
        return $this->config['defaultTransport'];
    }

    /**
     * @return Autowire[]
     */
    public function getTransports(): array
    {
        return array_map([Autowire::class, 'wire'], $this->config['transports']);
    }
}

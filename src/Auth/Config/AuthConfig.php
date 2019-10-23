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
use Spiral\Core\Exception\ConfigException;
use Spiral\Core\InjectableConfig;

/**
 * Manages auth http transport configuration.
 */
final class AuthConfig extends InjectableConfig
{
    public const CONFIG = 'auth';

    private $config = [
        'defaultTransport' => '',
        'transports'       => []
    ];

    /**
     * @return string
     */
    public function getDefaultTransport(): string
    {
        return $this->config['defaultTransport'];
    }

    /**
     * @return array
     */
    public function getTransports(): array
    {
        $transports = [];
        foreach ($this->config['transports'] as $transport) {
            if (is_object($transport) && !$transport instanceof Autowire) {
                $transports[] = $transport;
                continue;
            }

            $transports[] = $this->wire($transport);
        }

        return $transports;
    }

    /**
     * @param mixed $item
     * @return Autowire
     *
     * @throws ConfigException
     */
    private function wire($item): Autowire
    {
        if ($item instanceof Autowire) {
            return $item;
        }

        if (is_string($item)) {
            return new Autowire($item);
        }

        if (is_array($item) && isset($item['class'])) {
            return new Autowire($item['class'], $item['options'] ?? []);
        }

        throw new ConfigException('Invalid class reference in auth config');
    }
}

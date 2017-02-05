<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Debug\Configs;

use Spiral\Core\InjectableConfig;

class LogsConfig extends InjectableConfig
{
    /**
     * Associated config file.
     */
    const CONFIG = 'monolog';

    /**
     * @param string $channel
     *
     * @return bool
     */
    public function hasHandlers(string $channel): bool
    {
        return isset($this->config[$channel]);
    }

    /**
     * Definition of log handler definitions for specific channel.
     *
     * @param string $channel
     *
     * @return array
     */
    public function logHandlers(string $channel): array
    {
        return $this->config[$channel];
    }
}
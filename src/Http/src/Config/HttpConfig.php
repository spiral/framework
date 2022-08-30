<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;

final class HttpConfig extends InjectableConfig
{
    public const CONFIG = 'http';

    /**
     * @var array
     */
    protected $config = [
        'basePath'   => '/',
        'headers'    => [
            'Content-Type' => 'text/html; charset=UTF-8',
        ],
        'middleware' => [],
        'chunkSize' => null,
    ];

    public function getBasePath(): string
    {
        return $this->config['basePath'];
    }

    /**
     * Initial set of headers.
     */
    public function getBaseHeaders(): array
    {
        return $this->config['headers'];
    }

    /**
     * Initial middleware set.
     *
     * @return array|Autowire[]
     */
    public function getMiddleware(): array
    {
        return $this->config['middleware'] ?? $this->config['middlewares'];
    }

    /**
     * If chunkSize isn't provided - using default values
     */
    public function getChunkSize(): ?int
    {
        if (\is_null($this->config['chunkSize']) || (int) $this->config['chunkSize'] < 0) {
            return null;
        }

        return (int) $this->config['chunkSize'];
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Prototype\Config;

use Spiral\Core\InjectableConfig;

/**
 * @psalm-type TBinding = class-string|array{resolve: class-string, with: array}
 */
final class PrototypeConfig extends InjectableConfig
{
    public const CONFIG = 'prototype';

    /**
     * @var array{
     *     bindings: array<non-empty-string, TBinding>
     * }
     */
    protected array $config = [
        'bindings' => [],
    ];

    /**
     * @return array<non-empty-string, TBinding>
     */
    public function getBindings(): array
    {
        return (array) ($this->config['bindings'] ?? []);
    }
}

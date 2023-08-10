<?php

declare(strict_types=1);

namespace Spiral\Filters\Config;

use Spiral\Core\InjectableConfig;

final class FiltersConfig extends InjectableConfig
{
    public const CONFIG = 'filters';

    protected array $config = [
        'interceptors' => [],
        'validationInterceptors' => [],
    ];

    public function getInterceptors(): array
    {
        return (array)($this->config['interceptors'] ?? []);
    }

    public function getValidationInterceptors(): array
    {
        return (array)($this->config['validationInterceptors'] ?? []);
    }
}

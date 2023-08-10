<?php

declare(strict_types=1);

namespace Spiral\Filters\Config;

use Spiral\Core\InjectableConfig;

final class FiltersConfig extends InjectableConfig
{
    public const CONFIG = 'filters';

    protected array $config = [
        'interceptors' => [],
    ];

    public function getInterceptors(): array
    {
        return (array)($this->config['interceptors'] ?? []);
    }
}

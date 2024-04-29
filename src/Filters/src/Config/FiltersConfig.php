<?php

declare(strict_types=1);

namespace Spiral\Filters\Config;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\InjectableConfig;
use Spiral\Interceptors\InterceptorInterface;

final class FiltersConfig extends InjectableConfig
{
    public const CONFIG = 'filters';

    protected array $config = [
        'interceptors' => [],
    ];

    /**
     * @return array<class-string<CoreInterceptorInterface|InterceptorInterface>>
     */
    public function getInterceptors(): array
    {
        return (array)($this->config['interceptors'] ?? []);
    }
}

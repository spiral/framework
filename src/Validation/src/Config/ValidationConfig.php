<?php

declare(strict_types=1);

namespace Spiral\Validation\Config;

use Spiral\Core\InjectableConfig;

final class ValidationConfig extends InjectableConfig
{
    public const CONFIG = 'validation';

    protected array $config = [
        'defaultValidator' => null,
    ];

    public function getDefaultValidator(): ?string
    {
        return $this->config['defaultValidator'];
    }
}

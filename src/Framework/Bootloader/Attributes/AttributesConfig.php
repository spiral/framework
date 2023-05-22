<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Attributes;

use Spiral\Core\InjectableConfig;

final class AttributesConfig extends InjectableConfig
{
    public const CONFIG = 'attributes';

    protected array $config = [
        'annotations' => [
            'support' => true,
        ],
    ];

    public function isAnnotationsReaderEnabled(): bool
    {
        return (bool)$this->config['annotations']['support'];
    }
}

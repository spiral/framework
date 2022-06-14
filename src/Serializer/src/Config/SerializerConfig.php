<?php

declare(strict_types=1);

namespace Spiral\Serializer\Config;

use Spiral\Core\InjectableConfig;

final class SerializerConfig extends InjectableConfig
{
    public const CONFIG = 'serializer';
    public const DEFAULT_SERIALIZER = 'json';

    protected array $config = [
        'default' => self::DEFAULT_SERIALIZER,
        'serializers' => [],
    ];

    /**
     * Get registered serializers.
     */
    public function getSerializers(): array
    {
        return $this->config['serializers'];
    }

    /**
     * Get name (format) of the default serializer.
     */
    public function getDefault(): string
    {
        return $this->config['default'];
    }
}

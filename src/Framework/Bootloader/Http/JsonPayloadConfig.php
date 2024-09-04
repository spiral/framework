<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Core\InjectableConfig;

class JsonPayloadConfig extends InjectableConfig
{
    public const CONFIG = 'json-payload';

    /** @var array */
    protected array $config = [
        'contentTypes' => [
            'application/json',
        ],
    ];

    /**
     * @return array<string>
     */
    public function getContentTypes(): array
    {
        return $this->config['contentTypes'];
    }
}

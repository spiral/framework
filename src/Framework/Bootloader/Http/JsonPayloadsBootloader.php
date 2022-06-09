<?php

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;

final class JsonPayloadsBootloader extends Bootloader
{
    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(HttpBootloader $http): void
    {
        $this->config->setDefaults(
            JsonPayloadConfig::CONFIG,
            [
                'contentTypes' => [
                    'application/json',
                ],
            ]
        );
    }

    /**
     * Add custom MIME type to be parsed as JSON.
     */
    public function addContentType(string $contentType): void
    {
        $this->config->modify(JsonPayloadConfig::CONFIG, new Append('contentTypes', null, $contentType));
    }
}

<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Http\Middleware\JsonPayloadMiddleware;

final class JsonPayloadsBootloader extends Bootloader
{
    /** @var array */
    protected const DEPENDENCIES = [
        HttpBootloader::class,
    ];

    /** @var ConfiguratorInterface */
    private $config;

    /**
     * JsonPayloadsBootloader constructor.
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param HttpBootloader $http
     */
    public function boot(HttpBootloader $http): void
    {
        $this->config->setDefaults(
            JsonPayloadConfig::CONFIG,
            [
                'contentTypes' => [
                    'application/json',
                ],
            ]
        );

        $http->addMiddleware(JsonPayloadMiddleware::class);
    }

    /**
     * Add custom MIME type to be parsed as JSON.
     *
     * @param string $contentType
     */
    public function addContentType(string $contentType): void
    {
        $this->config->modify(JsonPayloadConfig::CONFIG, new Append('contentTypes', null, $contentType));
    }
}

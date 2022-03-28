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
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Broadcast\BroadcastBootloader;
use Spiral\Broadcast\Middleware\WebsocketsMiddleware;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Config\Patch\Set;
use Spiral\Core\Container\SingletonInterface;

/**
 * @deprecated since v2.12. Will be removed in v3.0
 * Authorizes websocket and server connections using interceptor middleware.
 */
final class WebsocketsBootloader extends Bootloader implements SingletonInterface
{
    /**
     * @var array<Bootloader>
     */
    protected const DEPENDENCIES = [
        HttpBootloader::class,
        BroadcastBootloader::class,
    ];

    /**
     * @var ConfiguratorInterface
     */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param HttpBootloader $http
     * @param EnvironmentInterface $env
     */
    public function boot(HttpBootloader $http, EnvironmentInterface $env): void
    {
        $this->config->setDefaults('websockets', [
            'path'            => $env->get('RR_BROADCAST_PATH', null),
            'authorizeServer' => null,
            'authorizeTopics' => [],
        ]);

        if ($env->get('RR_BROADCAST_PATH', null) !== null) {
            $http->addMiddleware(WebsocketsMiddleware::class);
        }
    }

    /**
     * @param callable|null $callback
     */
    public function authorizeServer(?callable $callback): void
    {
        $this->config->modify('websockets', new Set('authorizeServer', $callback));
    }

    /**
     * @param string $topic
     * @param callable $callback
     */
    public function authorizeTopic(string $topic, callable $callback): void
    {
        $this->config->modify('websockets', new Append('authorizeTopics', $topic, $callback));
    }
}

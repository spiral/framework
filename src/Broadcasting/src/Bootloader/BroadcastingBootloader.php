<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\BroadcastManager;
use Spiral\Broadcasting\BroadcastManagerInterface;
use Spiral\Broadcasting\Config\BroadcastConfig;
use Spiral\Broadcasting\Driver\LogBroadcast;
use Spiral\Broadcasting\Driver\NullBroadcast;
use Spiral\Broadcasting\TopicRegistry;
use Spiral\Broadcasting\TopicRegistryInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;

final class BroadcastingBootloader extends Bootloader
{
    protected const SINGLETONS = [
        BroadcastManagerInterface::class => BroadcastManager::class,
        BroadcastInterface::class => [self::class, 'initDefaultBroadcast'],
        TopicRegistryInterface::class => [self::class, 'initTopicRegistry'],
    ];

    private ConfiguratorInterface $config;

    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    public function registerDriverAlias(string $driverClass, string $alias): void
    {
        $this->config->modify(
            BroadcastConfig::CONFIG,
            new Append('driverAliases', $alias, $driverClass)
        );
    }

    public function boot(EnvironmentInterface $env): void
    {
        $this->initConfig($env);
    }

    private function initConfig(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            BroadcastConfig::CONFIG,
            [
                'default' => $env->get('BROADCAST_CONNECTION', 'null'),
                'authorize' => [
                    'path' => $env->get('BROADCAST_AUTHORIZE_PATH'),
                    'topics' => [],
                ],
                'aliases' => [],
                'connections' => [
                    'null' => [
                        'driver' => 'null',
                    ],
                ],
                'driverAliases' => [
                    'null' => NullBroadcast::class,
                    'log' => LogBroadcast::class,
                ],
            ]
        );
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function initDefaultBroadcast(BroadcastManagerInterface $manager): BroadcastInterface
    {
        return $manager->connection();
    }

    /**
     * @noRector RemoveUnusedPrivateMethodRector
     */
    private function initTopicRegistry(BroadcastConfig $config): TopicRegistryInterface
    {
        return new TopicRegistry($config->getTopics());
    }
}

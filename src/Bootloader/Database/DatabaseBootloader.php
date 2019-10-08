<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Database;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Database\Database;
use Spiral\Database\DatabaseInterface;
use Spiral\Database\DatabaseManager;
use Spiral\Database\DatabaseProviderInterface;

final class DatabaseBootloader extends Bootloader implements SingletonInterface
{
    public const SINGLETONS = [
        DatabaseProviderInterface::class => DatabaseManager::class,
    ];

    public const BINDINGS = [
        DatabaseInterface::class => Database::class
    ];

    /** @var ConfiguratorInterface */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Init database config.
     */
    public function boot(): void
    {
        $this->config->setDefaults('database', [
            'default'   => 'default',
            'aliases'   => [],
            'databases' => [],
            'drivers'   => []
        ]);
    }
}

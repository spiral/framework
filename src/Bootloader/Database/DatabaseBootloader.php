<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Database;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Database\Database;
use Spiral\Database\DatabaseInterface;
use Spiral\Database\DatabaseManager;
use Spiral\Database\DatabaseProviderInterface;

final class DatabaseBootloader extends Bootloader implements SingletonInterface
{
    const SINGLETONS = [
        DatabaseProviderInterface::class => DatabaseManager::class
    ];

    const BINDINGS = [
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
     * @param ContainerInterface $container
     * @param FinalizerInterface $finalizer
     */
    public function boot(ContainerInterface $container, FinalizerInterface $finalizer)
    {
        $finalizer->addFinalizer(function ($terminate) use ($container) {
            if (!$terminate || !$container->has(DatabaseManager::class)) {
                return;
            }

            /** @var DatabaseManager $dbal */
            $dbal = $container->get(DatabaseManager::class);
            foreach ($dbal->getDrivers() as $driver) {
                $driver->disconnect();
            }
        });

        $this->config->setDefaults('database', [
            'default'   => 'default',
            'aliases'   => [],
            'databases' => [],
            'drivers'   => []
        ]);
    }
}
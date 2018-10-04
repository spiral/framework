<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Database\Database;
use Spiral\Database\DatabaseInterface;
use Spiral\Database\DatabaseManager;

class DatabaseBootloader extends Bootloader
{
    const BOOT = true;

    const BINDINGS = [
        DatabaseInterface::class => Database::class
    ];

    /**
     * @param ConfiguratorInterface $configurator
     * @param FinalizerInterface    $finalizer
     * @param ContainerInterface    $container
     */
    public function boot(
        ConfiguratorInterface $configurator,
        FinalizerInterface $finalizer,
        ContainerInterface $container
    ) {
        $configurator->setDefaults('database', [
            'aliases'   => [],
            'databases' => [],
            'drivers'   => []
        ]);

        $finalizer->addFinalizer(function () use ($container) {
            /** @var DatabaseManager $dbal */
            $dbal = $container->get(DatabaseManager::class);

            // close all database connections
            foreach ($dbal->getDrivers() as $driver) {
                $driver->disconnect();
            }
        });
    }
}
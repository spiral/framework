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
use Spiral\Database\DatabaseManager;

/**
 * Close all the connections after each serve() cycle.
 */
final class DisconnectsBootloader extends Bootloader
{
    /**
     * @param FinalizerInterface $finalizer
     * @param ContainerInterface $container
     */
    public function boot(FinalizerInterface $finalizer, ContainerInterface $container)
    {
        $finalizer->addFinalizer(function () use ($container) {
            /** @var DatabaseManager $dbal */
            $dbal = $container->get(DatabaseManager::class);
            foreach ($dbal->getDrivers() as $driver) {
                $driver->disconnect();
            }
        });
    }
}

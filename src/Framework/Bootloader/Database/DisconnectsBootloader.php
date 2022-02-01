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
use Cycle\Database\DatabaseManager;

/**
 * Close all the connections after each serve() cycle.
 *
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class DisconnectsBootloader extends Bootloader
{
    /**
     * @param FinalizerInterface $finalizer
     * @param ContainerInterface $container
     */
    public function boot(FinalizerInterface $finalizer, ContainerInterface $container): void
    {
        $finalizer->addFinalizer(
            function () use ($container): void {
                /** @var DatabaseManager $dbal */
                $dbal = $container->get(DatabaseManager::class);
                foreach ($dbal->getDrivers() as $driver) {
                    $driver->disconnect();
                }
            }
        );
    }
}

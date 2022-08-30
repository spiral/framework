<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Cycle;

use Cycle\ORM\Config\RelationConfig;
use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Transaction;
use Cycle\ORM\TransactionInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\FinalizerInterface;
use Spiral\Bootloader\Database\DatabaseBootloader;
use Spiral\Core\Container;
use Spiral\Cycle\RepositoryInjector;
use Cycle\Database\DatabaseProviderInterface;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class CycleBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        DatabaseBootloader::class,
        SchemaBootloader::class,
    ];

    protected const BINDINGS = [
        TransactionInterface::class => Transaction::class,
    ];

    protected const SINGLETONS = [
        ORMInterface::class     => ORM::class,
        ORM::class              => [self::class, 'orm'],
        FactoryInterface::class => [self::class, 'factory'],
    ];

    /**
     * @param Container          $container
     * @param FinalizerInterface $finalizer
     */
    public function boot(Container $container, FinalizerInterface $finalizer): void
    {
        $finalizer->addFinalizer(
            function () use ($container): void {
                if ($container->hasInstance(ORMInterface::class)) {
                    $container->get(ORMInterface::class)->getHeap()->clean();
                }
            }
        );

        $container->bindInjector(RepositoryInterface::class, RepositoryInjector::class);
    }

    /**
     * @param FactoryInterface             $factory
     * @param SchemaInterface              $schema
     * @param PromiseFactoryInterface|null $promiseFactory
     * @return ORMInterface
     */
    private function orm(
        FactoryInterface $factory,
        SchemaInterface $schema = null,
        PromiseFactoryInterface $promiseFactory = null
    ): ORMInterface {
        $orm = new ORM($factory, $schema);

        if ($promiseFactory !== null) {
            return $orm->withPromiseFactory($promiseFactory);
        }

        return $orm;
    }

    /**
     * @param DatabaseProviderInterface $dbal
     * @param Container                 $container
     * @return FactoryInterface
     */
    private function factory(DatabaseProviderInterface $dbal, Container $container): FactoryInterface
    {
        return new Factory($dbal, RelationConfig::getDefault(), $container, $container);
    }
}

<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Cycle;

use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Transaction;
use Cycle\ORM\TransactionInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Core\Container;
use Spiral\Cycle\SelectInjector;
use Spiral\Database\DatabaseProviderInterface;

final class CycleBootloader extends Bootloader implements DependedInterface
{
    public const BINDINGS = [
        RepositoryInterface::class  => [self::class, 'repository'],
        TransactionInterface::class => Transaction::class
    ];

    public const SINGLETONS = [
        ORMInterface::class     => [self::class, 'orm'],
        FactoryInterface::class => [self::class, 'factory'],
    ];

    /**
     * @param Container          $container
     * @param FinalizerInterface $finalizer
     */
    public function boot(Container $container, FinalizerInterface $finalizer)
    {
        $finalizer->addFinalizer(function () use ($container) {
            if ($container->hasInstance(ORMInterface::class)) {
                $container->get(ORMInterface::class)->getHeap()->clean();
            }
        });

        $container->bindInjector(Select::class, SelectInjector::class);
    }

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [
            SchemaBootloader::class
        ];
    }

    /**
     * @param ORMInterface $orm
     * @param string       $role
     * @return RepositoryInterface
     */
    protected function repository(ORMInterface $orm, string $role = null): RepositoryInterface
    {
        return $orm->getRepository($role);
    }

    /**
     * @param FactoryInterface             $factory
     * @param SchemaInterface              $schema
     * @param PromiseFactoryInterface|null $promiseFactory
     * @return ORMInterface
     */
    protected function orm(
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
     * @return FactoryInterface
     */
    protected function factory(DatabaseProviderInterface $dbal): FactoryInterface
    {
        return new Factory($dbal);
    }
}
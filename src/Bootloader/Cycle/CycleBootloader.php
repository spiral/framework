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
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Transaction;
use Cycle\ORM\TransactionInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Bootloader\Database\DatabaseBootloader;
use Spiral\Core\Container;
use Spiral\Cycle\SelectInjector;
use Spiral\Database\DatabaseProviderInterface;

final class CycleBootloader extends Bootloader implements DependedInterface
{
    public const BINDINGS = [
        TransactionInterface::class => Transaction::class,
    ];

    public const SINGLETONS = [
        ORMInterface::class     => [self::class, 'orm'],
        FactoryInterface::class => [self::class, 'factory'],
    ];

    /**
     * @param Container            $container
     * @param FinalizerInterface   $finalizer
     * @param SchemaInterface|null $schema
     */
    public function boot(Container $container, FinalizerInterface $finalizer, SchemaInterface $schema = null)
    {
        $finalizer->addFinalizer(function () use ($container) {
            if ($container->hasInstance(ORMInterface::class)) {
                $container->get(ORMInterface::class)->getHeap()->clean();
            }
        });

        $container->bindInjector(Select::class, SelectInjector::class);

        if ($schema !== null) {
            $this->bindRepositories($container, $schema);
        }
    }

    /**
     * Create container bindings to resolve repositories by they class names.
     *
     * @param Container       $container
     * @param SchemaInterface $schema
     */
    public function bindRepositories(Container $container, SchemaInterface $schema)
    {
        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, SchemaInterface::REPOSITORY);
            if ($repository === Select\Repository::class || $repository === null) {
                // default repository can not be wired
                continue;
            }

            // initiate all repository dependencies using factory method forwarded to ORM
            $container->bindSingleton($repository, function (ORMInterface $orm) use ($role) {
                return $orm->getRepository($role);
            });
        }
    }

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [
            DatabaseBootloader::class,
            SchemaBootloader::class
        ];
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
     * @param Container                 $container
     * @return FactoryInterface
     */
    protected function factory(DatabaseProviderInterface $dbal, Container $container): FactoryInterface
    {
        return new Factory($dbal, RelationConfig::getDefault(), $container, $container);
    }
}

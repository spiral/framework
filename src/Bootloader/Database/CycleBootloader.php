<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Database;

use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\PromiseFactoryInterface;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select\Repository;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\TokenizerBootloader;
use Spiral\Core\Container;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Database\DatabaseProviderInterface;

final class CycleBootloader extends Bootloader implements DependedInterface, SingletonInterface
{
    public const SINGLETONS = [
        ORMInterface::class        => [self::class, 'orm'],
        SchemaInterface::class     => [self::class, 'schema'],
        FactoryInterface::class    => [self::class, 'factory'],
        RepositoryInterface::class => [self::class, 'repository']
    ];

    /**
     * @param Container       $container
     * @param SchemaInterface $schema
     */
    public function boot(Container $container, SchemaInterface $schema = null)
    {
        if ($schema === null) {
            return;
        }

        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, Schema::REPOSITORY);
            if ($repository === Repository::class || $repository === null) {
                // default repository can not be wired
                continue;
            }

            // initiate all repository dependencies using factory method forwarded to ORM
            $container->bind(
                $repository,
                new Container\Autowire(RepositoryInterface::class, ['role' => $role])
            );
        }
    }

    // todo: compile sequence ?

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [
            TokenizerBootloader::class,
            DatabaseBootloader::class
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
     * @param MemoryInterface $memory
     * @return SchemaInterface|null
     */
    protected function schema(MemoryInterface $memory): ?SchemaInterface
    {
        $schema = $memory->loadData('orm');
        if (is_null($schema)) {
            return null;
        }

        // the ORM schema must be compiled in background (todo: for now)

        return new Schema($schema);
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
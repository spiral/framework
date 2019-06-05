<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Cycle;

use Cycle\Annotated;
use Cycle\ORM\RepositoryInterface;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select\Repository;
use Cycle\Schema\Generator;
use Cycle\Schema\GeneratorInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Database\DatabaseBootloader;
use Spiral\Bootloader\TokenizerBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Tokenizer\ClassesInterface;

final class SchemaBootloader extends Bootloader implements DependedInterface, Container\SingletonInterface
{
    public const BINDINGS = [
        SchemaInterface::class             => [self::class, 'schema'],

        // annotated entities
        Annotated\Entities::class          => [self::class, 'entities'],
        Annotated\MergeColumns::class      => [self::class, 'mergeColumns'],
        Annotated\MergeIndexes::class      => [self::class, 'mergeIndexes'],

        // relations
        Generator\GenerateRelations::class => [self::class, 'relations'],
    ];

    /** @var Container */
    private $container;

    /** @var ConfiguratorInterface */
    private $generators;

    /**
     * CycleSchemaBootloader constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->generators = [
            Annotated\Embeddings::class,
            Annotated\Entities::class,
            Generator\ResetTables::class,
            Annotated\MergeColumns::class,
            Generator\GenerateRelations::class,
            Generator\ValidateEntities::class,
            Generator\RenderTables::class,
            Generator\RenderRelations::class,
            Annotated\MergeIndexes::class,
            Generator\GenerateTypecast::class
        ];
    }

    /**
     * @param SchemaInterface|null $schema
     */
    public function boot(SchemaInterface $schema = null)
    {
        if (!is_null($schema)) {
            $this->bootRepositories($schema);
        }
    }

    /**
     * @param SchemaInterface $schema
     */
    public function bootRepositories(SchemaInterface $schema)
    {
        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, Schema::REPOSITORY);
            if ($repository === Repository::class || $repository === null) {
                // default repository can not be wired
                continue;
            }

            // initiate all repository dependencies using factory method forwarded to ORM
            $this->container->bind(
                $repository,
                new Container\Autowire(RepositoryInterface::class, ['role' => $role])
            );
        }
    }

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
     * @param mixed $generator
     */
    public function addGenerator($generator)
    {
        $this->generators[] = $generator;
    }

    /**
     * @return GeneratorInterface[]
     */
    public function getGenerators(): array
    {
        $result = [];
        foreach ($this->generators as $generator) {
            if (is_object($generator) && !$generator instanceof Container\Autowire) {
                $result[] = $generator;
            } else {
                $result[] = $this->container->get($generator);
            }
        }

        return $result;
    }

    /**
     * @param MemoryInterface $memory
     * @return SchemaInterface|null
     */
    protected function schema(MemoryInterface $memory): ?SchemaInterface
    {
        $schema = $memory->loadData('cycle');
        if (is_null($schema)) {
            return null;
        }

        return new Schema($schema);
    }

    /**
     * @param ClassesInterface $classes
     * @return Annotated\Entities
     */
    protected function entities(ClassesInterface $classes): Annotated\Entities
    {
        return new Annotated\Entities($classes);
    }

    /**
     * @return Annotated\MergeColumns
     */
    protected function mergeColumns(): Annotated\MergeColumns
    {
        return new Annotated\MergeColumns();
    }

    /**
     * @return Annotated\MergeIndexes
     */
    protected function mergeIndexes(): Annotated\MergeIndexes
    {
        return new Annotated\MergeIndexes();
    }

    /**
     * @return Generator\GenerateRelations
     */
    protected function relations(): Generator\GenerateRelations
    {
        return new Generator\GenerateRelations();
    }
}
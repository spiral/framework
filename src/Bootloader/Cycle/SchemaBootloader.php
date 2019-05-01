<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Cycle;

use Cycle\Annotated\Columns;
use Cycle\Annotated\Entities;
use Cycle\Annotated\Indexes;
use Cycle\ORM\Schema;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\GeneratorInterface;
use Psr\Container\ContainerInterface;
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
        SchemaInterface::class   => [self::class, 'schema'],

        // annotated entities
        Entities::class          => [self::class, 'entities'],
        Columns::class           => [self::class, 'columns'],
        Indexes::class           => [self::class, 'indexes'],

        // relations
        GenerateRelations::class => [self::class, 'relations'],
    ];

    /** @var ContainerInterface */
    private $container;

    /** @var ConfiguratorInterface */
    private $generators;

    /**
     * CycleSchemaBootloader constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->generators = [
            Entities::class,
            ResetTables::class,
            Columns::class,
            GenerateRelations::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            Indexes::class,
            GenerateTypecast::class
        ];
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
     * @return Entities
     */
    protected function entities(ClassesInterface $classes): Entities
    {
        return new Entities($classes);
    }

    /**
     * @return Columns
     */
    protected function columns(): Columns
    {
        return new Columns();
    }

    /**
     * @return Indexes
     */
    protected function indexes(): Indexes
    {
        return new Indexes();
    }

    /**
     * @return GenerateRelations
     */
    protected function relations(): GenerateRelations
    {
        return new GenerateRelations();
    }
}
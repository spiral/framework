<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Cycle;

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

final class SchemaBootloader extends Bootloader implements DependedInterface, Container\SingletonInterface
{
    public const GROUP_INDEX       = 'index';
    public const GROUP_RENDER      = 'render';
    public const GROUP_POSTPROCESS = 'postprocess';

    public const BINDINGS = [
        SchemaInterface::class             => [self::class, 'schema'],

        // relations
        Generator\GenerateRelations::class => [self::class, 'relations'],
    ];

    /** @var Container */
    private $container;

    /** @var ConfiguratorInterface */
    private $generators = [];

    /**
     * CycleSchemaBootloader constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->generators = [
            self::GROUP_INDEX       => [
                // find available entities
            ],
            self::GROUP_RENDER      => [
                // render tables and relations
                Generator\ResetTables::class,
                Generator\GenerateRelations::class,
                Generator\ValidateEntities::class,
                Generator\RenderTables::class,
                Generator\RenderRelations::class,
            ],
            self::GROUP_POSTPROCESS => [
                // post processing
                Generator\GenerateTypecast::class
            ],
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
     * @param string $group
     * @param mixed  $generator
     */
    public function addGenerator(string $group, $generator)
    {
        $this->generators[$group][] = $generator;
    }

    /**
     * @return GeneratorInterface[]
     */
    public function getGenerators(): array
    {
        $result = [];
        foreach ($this->generators as $group) {
            foreach ($group as $generator) {
                if (is_object($generator) && !$generator instanceof Container\Autowire) {
                    $result[] = $generator;
                } else {
                    $result[] = $this->container->get($generator);
                }
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
     * @return Generator\GenerateRelations
     */
    protected function relations(): Generator\GenerateRelations
    {
        return new Generator\GenerateRelations();
    }
}
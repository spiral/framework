<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Cycle;

use Cycle\ORM\SchemaInterface;
use Cycle\Schema\Generator;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\MemoryInterface;
use Spiral\Core\Container;
use Spiral\Cycle\SchemaCompiler;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class SchemaBootloader extends Bootloader implements Container\SingletonInterface
{
    public const GROUP_INDEX       = 'index';
    public const GROUP_RENDER      = 'render';
    public const GROUP_POSTPROCESS = 'postprocess';

    protected const DEPENDENCIES = [
        TokenizerBootloader::class,
    ];

    protected const BINDINGS = [
        SchemaInterface::class             => [self::class, 'schema'],
        Generator\GenerateRelations::class => [self::class, 'relationGenerator'],
    ];

    /** @var Container */
    private $container;

    /** @var string[][]|GeneratorInterface[][] */
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
                Generator\GenerateTypecast::class,
            ],
        ];
    }

    /**
     * @param string $group
     * @param mixed  $generator
     */
    public function addGenerator(string $group, $generator): void
    {
        $this->generators[$group][] = $generator;
    }

    /**
     * @return GeneratorInterface[]
     * @throws \Throwable
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
     * @return SchemaInterface
     *
     * @throws \Throwable
     */
    protected function schema(MemoryInterface $memory): SchemaInterface
    {
        $schemaCompiler = SchemaCompiler::fromMemory($memory);
        if ($schemaCompiler->isEmpty()) {
            $schemaCompiler = SchemaCompiler::compile(
                $this->container->get(Registry::class),
                $this->getGenerators()
            );
            $schemaCompiler->toMemory($memory);
        }

        return $schemaCompiler->toSchema();
    }

    /**
     * @return Generator\GenerateRelations
     */
    protected function relationGenerator(): Generator\GenerateRelations
    {
        return new Generator\GenerateRelations();
    }
}

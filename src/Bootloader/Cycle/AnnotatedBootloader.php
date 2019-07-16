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
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Tokenizer\ClassesInterface;

final class AnnotatedBootloader extends Bootloader implements DependedInterface
{
    public const BINDINGS = [
        // annotated entities
        Annotated\Embeddings::class   => [self::class, 'embeddings'],
        Annotated\Entities::class     => [self::class, 'entities'],
        Annotated\MergeColumns::class => [self::class, 'mergeColumns'],
        Annotated\MergeIndexes::class => [self::class, 'mergeIndexes'],
    ];

    /**
     * @param SchemaBootloader $cycle
     */
    public function boot(SchemaBootloader $cycle)
    {
        $cycle->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Embeddings::class);
        $cycle->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Entities::class);
        $cycle->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\MergeColumns::class);
        $cycle->addGenerator(SchemaBootloader::GROUP_RENDER, Annotated\MergeIndexes::class);
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
     * @param ClassesInterface $classes
     * @return Annotated\Embeddings
     */
    protected function embeddings(ClassesInterface $classes): Annotated\Embeddings
    {
        return new Annotated\Embeddings($classes);
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
}
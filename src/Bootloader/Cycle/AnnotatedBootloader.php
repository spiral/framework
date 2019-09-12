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
use Doctrine\Common\Annotations\AnnotationRegistry;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Bootloader\DependedInterface;

final class AnnotatedBootloader extends Bootloader implements DependedInterface
{
    /**
     * @param SchemaBootloader $schema
     */
    public function boot(SchemaBootloader $schema)
    {
        AnnotationRegistry::registerLoader('class_exists');

        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Embeddings::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Entities::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\MergeColumns::class);
        $schema->addGenerator(SchemaBootloader::GROUP_RENDER, Annotated\MergeIndexes::class);
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
}

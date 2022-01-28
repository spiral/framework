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

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class AnnotatedBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        SchemaBootloader::class,
    ];

    /**
     * @param SchemaBootloader $schema
     */
    public function boot(SchemaBootloader $schema): void
    {
        AnnotationRegistry::registerLoader('class_exists');

        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Embeddings::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\Entities::class);
        $schema->addGenerator(SchemaBootloader::GROUP_INDEX, Annotated\MergeColumns::class);
        $schema->addGenerator(SchemaBootloader::GROUP_RENDER, Annotated\MergeIndexes::class);
    }
}

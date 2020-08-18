<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot;

/**
 * Nullable memory interface (does not save or load anything).
 */
final class NullMemory implements MemoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadData(string $section)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function saveData(string $section, $data): void
    {
        //Nothing to do
    }
}

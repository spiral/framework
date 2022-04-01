<?php

declare(strict_types=1);

namespace Spiral\Boot;

/**
 * Nullable memory interface (does not save or load anything).
 */
final class NullMemory implements MemoryInterface
{
    public function loadData(string $section): mixed
    {
        return null;
    }

    public function saveData(string $section, $data): void
    {
        //Nothing to do
    }
}

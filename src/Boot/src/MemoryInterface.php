<?php

declare(strict_types=1);

namespace Spiral\Boot;

/**
 * Long memory cache. Use this storage to remember results of your calculations, do not store user
 * or non-static data in here (!).
 */
interface MemoryInterface
{
    /**
     * Read data from long memory cache. Must return exacts same value as saved or null. Current
     * convention allows to store serializable (var_export-able) data.
     *
     * @param string $section Non case sensitive.
     */
    public function loadData(string $section): mixed;

    /**
     * Put data to long memory cache. No inner references or closures are allowed. Current
     * convention allows to store serializable (var_export-able) data.
     *
     * @param string       $section Non case sensitive.
     */
    public function saveData(string $section, mixed $data): void;
}

<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use ReflectionClass;

/**
 * Interface locator interface.
 */
interface InterfacesInterface
{
    /**
     * Index all available files and generate list of found interfaces with their names and filenames.
     * Unreachable interfaces or files with conflicts must be skipped. This is SLOW method, should be
     * used only for static analysis.
     *
     * @param class-string|null $target Interface parent. By default - null (all interfaces).
     *                                  Parent (interface) will also be included to interfaces list as one of
     *                                  results.
     * @return array<class-string, ReflectionClass>
     */
    public function getInterfaces(string|null $target = null): array;
}

<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use ReflectionClass;

/**
 * Class locator interface.
 */
interface ClassesInterface
{
    /**
     * Index all available files and generate list of found classes with their names and filenames.
     * Unreachable classes or files with conflicts must be skipped. This is SLOW method, should be
     * used only for static analysis.
     *
     * @param object|class-string|null $target  Class, interface or trait parent. By default - null (all classes).
     *                                          Parent (class) will also be included to classes list as one of
     *                                          results.
     * @return array<class-string, ReflectionClass>
     */
    public function getClasses(object|string|null $target = null): array;
}

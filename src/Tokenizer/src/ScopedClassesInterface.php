<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

interface ScopedClassesInterface
{
    /**
     * Index all available files and generate list of found classes for given scope with their names and filenames.
     * Unreachable classes or files with conflicts must be skipped. This is SLOW method, should be
     * used only for static analysis.
     *
     * @param string $scope               Scope name. If scope is not exist, global settings will be used.
     * @param object|string|null $target  Class, interface or trait parent. By default - null (all classes).
     *                                    Parent (class) will also be included to classes list as one of
     *                                    results.
     * @return \ReflectionClass[]
     */
    public function getScopedClasses(string $scope, object|string|null $target = null): array;
}

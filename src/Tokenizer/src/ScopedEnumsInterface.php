<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

interface ScopedEnumsInterface
{
    /**
     * Index all available files and generate list of found enums for given scope with their names and filenames.
     * Unreachable enums or files with conflicts must be skipped. This is SLOW method, should be
     * used only for static analysis.
     *
     * @param string $scope               Scope name. If scope is not exist, global settings will be used.
     * @param object|string|null $target  Enum, interface or trait parent. By default - null (all classes).
     *
     * @return \ReflectionEnum[]
     */
    public function getScopedEnums(string $scope, object|string|null $target = null): array;
}

<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

interface ScopedInterfacesInterface
{
    /**
     * Index all available files and generate list of found interfaces for given scope with their names and filenames.
     * Unreachable interfaces or files with conflicts must be skipped. This is SLOW method, should be
     * used only for static analysis.
     *
     * @param string $scope       Scope name. If scope is not exist, global settings will be used.
     * @param string|null $target Interface parent. By default - null (all interfaces).
     *                            Parent (interface) will also be included to interfaces list as one of
     *                            results.
     * @return \ReflectionClass[]
     */
    public function getScopedInterfaces(string $scope, string|null $target = null): array;
}

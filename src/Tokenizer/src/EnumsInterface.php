<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use ReflectionEnum;

/**
 * Enum locator interface.
 */
interface EnumsInterface
{
    /**
     * Index all available files and generate list of found enums with their names and filenames.
     * Unreachable enums or files with conflicts must be skipped. This is SLOW method, should be
     * used only for static analysis.
     *
     * @param object|class-string|null $target  Enum, interface or trait parent. By default - null (all enums).
     *
     * @return array<class-string, ReflectionEnum>
     */
    public function getEnums(object|string|null $target = null): array;
}

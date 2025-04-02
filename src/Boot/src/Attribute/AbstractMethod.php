<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

/**
 * Abstract base class for method attributes used in bootloaders.
 * 
 * This abstract class serves as a base for all method-level attributes in the bootloader system,
 * providing common functionality for managing binding aliases.
 *
 * @internal This class is for internal use within the framework and should not be used directly by application code.
 */
abstract class AbstractMethod
{
    /**
     * @param non-empty-string|null $alias Alias for the method. If not provided, the return type of the method will be used as the alias.
     * @param bool $aliasesFromReturnType Add aliases from the return type of the method even if the method has an alias.
     */
    public function __construct(
        /**
         * Alias for the method.
         * If not provided, the return type of the method will be used as the alias.
         */
        public readonly ?string $alias = null,
        /**
         * Add aliases from the return type of the method even if the method has an alias.
         */
        public readonly bool $aliasesFromReturnType = false,
    ) {}
}

<?php

declare(strict_types=1);

namespace Spiral\DataGrid;

use Spiral\DataGrid\Exception\SpecificationException;

/**
 * Provides the ability to write the specification to a given source.
 */
interface WriterInterface
{
    /**
     * Render the specification and return altered source or null if specification can not be applied.
     *
     * @throws SpecificationException
     */
    public function write(mixed $source, SpecificationInterface $specification, Compiler $compiler): mixed;
}

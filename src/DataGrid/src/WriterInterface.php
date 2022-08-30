<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

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
     * @param mixed                  $source
     * @return mixed|null
     * @throws SpecificationException
     */
    public function write($source, SpecificationInterface $specification, Compiler $compiler);
}

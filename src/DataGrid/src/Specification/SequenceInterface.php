<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification;

use Spiral\DataGrid\SpecificationInterface;

/**
 * Sequence interface provides the ability to wrap multiple specifications inside one object.
 */
interface SequenceInterface extends SpecificationInterface
{
    /**
     * Return underlying specifications.
     *
     * @return SpecificationInterface[]
     */
    public function getSpecifications(): array;
}

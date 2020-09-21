<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\DataGrid\Fixture;

use Spiral\DataGrid\Specification\SequenceInterface;
use Spiral\DataGrid\SpecificationInterface;

/**
 * Mocks public value and set of underlying specifications.
 */
class Sequence implements SequenceInterface
{
    /** @var array */
    private $value;

    /** @var SpecificationInterface[] */
    private $specifications;

    /**
     * @param array                  $value
     * @param SpecificationInterface ...$specifications
     */
    public function __construct(array $value, SpecificationInterface ...$specifications)
    {
        $this->value = $value;
        $this->specifications = $specifications;
    }

    /**
     * @return SpecificationInterface[]
     */
    public function getSpecifications(): array
    {
        return $this->specifications;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }
}

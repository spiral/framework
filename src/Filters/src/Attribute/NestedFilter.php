<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\Model\FilterInterface;

/**
 * The attribute provides the ability to create nested filters. To demonstrate the composition, we will use a sample
 * filter:
 *
 *      class ProfileFilter extends Filter
 *      {
 *          #[\Spiral\Filters\Attribute\NestedFilter(class: AddressFilter::class)]
 *          public AddressFilter $address;
 *      }
 *
 * After creating nested filter it will be validated.
 */
#[Attribute(Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class NestedFilter
{
    /**
     * @param class-string<FilterInterface> $class
     */
    public function __construct(
        public readonly string $class,
        public readonly ?string $prefix = null
    ) {
    }

    public function getSchema(\ReflectionProperty $property): string|array
    {
        if ($this->prefix) {
            return [$this->class, $this->prefix];
        }

        return $this->class;
    }
}

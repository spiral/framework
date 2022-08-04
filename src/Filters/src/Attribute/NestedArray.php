<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\Attribute\Input\AbstractInput;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\InputInterface;

/**
 * The attribute provides the ability to create nested array of filters. To demonstrate the composition, we will use
 * a sample filter:
 *
 *      class ProfileFilter extends Filter
 *      {
 *          #[\Spiral\Filters\Attribute\NestedArray(
 *              class: AddressFilter::class,
 *              input: new \Spiral\Filters\Attribute\Input\Post(key: 'addresses')
 *          )]
 *          public array $addresses = [];
 *      }
 *
 * After creating nested filters they will be validated.
 */
#[Attribute(Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class NestedArray
{
    /**
     * @param class-string<FilterInterface> $class
     * @param non-empty-string|null $prefix
     */
    public function __construct(
        public readonly string $class,
        public readonly AbstractInput $input,
        public readonly ?string $prefix = null
    ) {
    }

    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $this->input->getValue($input, $property);
    }

    public function getSchema(\ReflectionProperty $property): array
    {
        if ($this->prefix) {
            return [$this->class, $this->prefix];
        }

        return [$this->class];
    }
}

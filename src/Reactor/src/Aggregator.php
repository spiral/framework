<?php

declare(strict_types=1);

namespace Spiral\Reactor;

use Spiral\Reactor\Exception\ReactorException;

/**
 * Provides ability to aggregate specific set of elements (type constrained), render them or
 * apply set of operations.
 *
 * @template TElement of AggregableInterface
 * @implements \IteratorAggregate<array-key, TElement>
 * @implements \ArrayAccess<array-key, TElement>
 */
class Aggregator implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @param TElement[] $elements
     */
    public function __construct(
        private readonly array $allowed,
        private array $elements = []
    ) {
    }

    /**
     * Get element by it's name.
     *
     * @param non-empty-string $name
     *
     * @return TElement
     * @throws ReactorException
     *
     * TODO add parameter type
     */
    public function __get($name): AggregableInterface
    {
        return $this->get($name);
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return \count($this->elements);
    }

    /**
     * Check if aggregation has named element with given name.
     *
     * @param non-empty-string $name
     */
    public function has(string $name): bool
    {
        foreach ($this->elements as $element) {
            if ($element instanceof NamedInterface && $element->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add new element.
     *
     * @param TElement $element
     */
    public function add(AggregableInterface $element): self
    {
        $reflector = new \ReflectionObject($element);

        $allowed = false;
        foreach ($this->allowed as $class) {
            /** @psalm-suppress RedundantCondition https://github.com/vimeo/psalm/issues/9489 */
            if ($reflector->isSubclassOf($class) || $element::class === $class) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            $type = $element::class;
            throw new ReactorException(\sprintf("Elements with type '%s' are not allowed", $type));
        }

        $this->elements[] = $element;

        return $this;
    }

    /**
     * Get named element by it's name.
     *
     * @param non-empty-string $name
     *
     * @return TElement
     * @throws ReactorException
     */
    public function get(string $name): AggregableInterface
    {
        return $this->find($name);
    }

    /**
     * Remove element by it's name.
     *
     * @param non-empty-string $name
     */
    public function remove(string $name): self
    {
        foreach ($this->elements as $index => $element) {
            if ($element instanceof NamedInterface && $element->getName() === $name) {
                unset($this->elements[$index]);
            }
        }

        return $this;
    }

    /**
     * @return \ArrayIterator<array-key, TElement>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @return TElement
     */
    public function offsetGet(mixed $offset): AggregableInterface
    {
        return $this->get($offset);
    }

    /**
     * @param TElement $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->remove($offset)->add($value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * Find element by it's name (NamedDeclarations only).
     *
     * @param non-empty-string $name
     *
     * @return TElement
     * @throws ReactorException When unable to find.
     */
    protected function find(string $name): AggregableInterface
    {
        foreach ($this->elements as $element) {
            if ($element instanceof NamedInterface && $element->getName() === $name) {
                return $element;
            }
        }

        throw new ReactorException(\sprintf("Unable to find element '%s'", $name));
    }
}

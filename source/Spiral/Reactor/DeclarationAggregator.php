<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\Exceptions\ReactorException;
use Spiral\Reactor\Prototypes\Declaration;
use Spiral\Reactor\Prototypes\NamedDeclaration;

/**
 * Provides ability to aggregate specific set of elements (type constrained), render them or
 * apply set of operations.
 */
class DeclarationAggregator extends Declaration implements
    \ArrayAccess,
    \IteratorAggregate,
    ReplaceableInterface
{
    /**
     * @var array
     */
    private $allowed = [];

    /**
     * @var RenderableInterface[]
     */
    private $elements = [];

    /**
     * @param array $allowed
     * @param array $elements
     */
    public function __construct(array $allowed, array $elements = [])
    {
        $this->allowed = $allowed;
        $this->elements = $elements;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->elements);
    }

    /**
     * Check if aggregation has named element with given name.
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        foreach ($this->elements as $element) {
            if ($element instanceof NamedDeclaration && $element->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add new element.
     *
     * @param RenderableInterface $element
     * @return $this
     * @throws ReactorException
     */
    public function add(RenderableInterface $element)
    {
        if (!in_array($type = get_class($element), $this->allowed)) {
            throw new ReactorException("Elements with type {$type} are not allowed.");
        }

        $this->elements[] = $element;

        return $this;
    }

    /**
     * Get named element by it's name.
     *
     * @param string $name
     * @return RenderableInterface
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new ReactorException("Undefined element '{$name}'.");
        }

        return $this->find($name);
    }

    /**
     * Remove element by it's name.
     *
     * @param string $name
     * @return $this
     */
    public function remove($name)
    {
        foreach ($this->elements as $index => $element) {
            if ($element instanceof NamedDeclaration && $element->getName() == $name) {
                unset($this->elements[$index]);
            }
        }

        return $this;
    }

    /**
     * Get element by it's name.
     *
     * @param string $name
     * @return RenderableInterface
     * @throws ReactorException
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->remove($offset)->add($value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function replace($search, $replace)
    {
        foreach ($this->elements as $element) {
            if ($element instanceof ReplaceableInterface) {
                $element->replace($search, $replace);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        $result = '';

        foreach ($this->elements as $element) {
            $result .= $element->render($indentLevel) . "\n\n";
        }

        return rtrim($result, "\n");
    }

    /**
     * Find element by it's name (NamedDeclarations only).
     *
     * @param string $name
     * @return RenderableInterface
     * @throws ReactorException
     */
    protected function find($name)
    {
        foreach ($this->elements as $element) {
            if ($element instanceof NamedDeclaration && $element->getName() == $name) {
                return $element;
            }
        }
        throw new ReactorException("Unable to find element '{$name}'.");
    }
}
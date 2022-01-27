<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Models;

use Spiral\Models\Exception\AccessException;
use Spiral\Models\Exception\AccessExceptionInterface;
use Spiral\Models\Exception\EntityException;

/**
 * AbstractEntity with ability to define field mutators and access
 */
abstract class AbstractEntity implements EntityInterface, ValueInterface, \IteratorAggregate
{
    /** @var array */
    private $fields;

    public function __construct(array $data = [])
    {
        $this->fields = $data;
    }

    /**
     * Destruct data entity.
     */
    public function __destruct()
    {
        $this->flushFields();
    }

    public function __isset(string $offset): bool
    {
        return $this->hasField($offset);
    }

    /**
     * @return mixed
     */
    public function __get(string $offset)
    {
        return $this->getField($offset);
    }

    /**
     * @param mixed $value
     */
    public function __set(string $offset, $value): void
    {
        $this->setField($offset, $value);
    }

    public function __unset(string $offset): void
    {
        unset($this->fields[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasField(string $name): bool
    {
        if (!array_key_exists($name, $this->fields)) {
            return false;
        }

        return $this->fields[$name] !== null || $this->isNullable($name);
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $filter If false, associated field setter or accessor will be ignored.
     *
     * @throws AccessException
     */
    public function setField(string $name, $value, bool $filter = true): void
    {
        if ($value instanceof ValueInterface) {
            //In case of non scalar values filters must be bypassed (check accessor compatibility?)
            $this->fields[$name] = clone $value;

            return;
        }

        if (!$filter || (is_null($value) && $this->isNullable($name))) {
            //Bypassing all filters
            $this->fields[$name] = $value;

            return;
        }

        //Checking if field have accessor
        $accessor = $this->getMutator($name, ModelSchema::MUTATOR_ACCESSOR);

        if ($accessor !== null) {
            //Setting value thought associated accessor
            $this->thoughValue($accessor, $name, $value);
        } else {
            //Setting value thought setter filter (if any)
            $this->setMutated($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $filter If false, associated field getter will be ignored.
     *
     * @throws AccessException
     */
    public function getField(string $name, $default = null, bool $filter = true)
    {
        $value = $this->hasField($name) ? $this->fields[$name] : $default;

        if ($value instanceof ValueInterface || (is_null($value) && $this->isNullable($name))) {
            //Direct access to value when value is accessor or null and declared as nullable
            return $value;
        }

        //Checking if field have accessor (decorator)
        $accessor = $this->getMutator($name, ModelSchema::MUTATOR_ACCESSOR);

        if (!empty($accessor)) {
            return $this->fields[$name] = $this->createValue($accessor, $name, $value);
        }

        //Getting value though getter
        return $this->getMutated($name, $filter, $value);
    }

    /**
     * @param iterable $fields
     * @param bool $all Fill all fields including non fillable.
     * @return $this
     *
     * @throws AccessException
     *
     * @see   $secured
     * @see   isFillable()
     * @see   $fillable
     */
    public function setFields(iterable $fields = [], bool $all = false)
    {
        if (!is_array($fields) && !$fields instanceof \Traversable) {
            return $this;
        }

        foreach ($fields as $name => $value) {
            if ($all || $this->isFillable($name)) {
                try {
                    $this->setField($name, $value, true);
                } catch (AccessExceptionInterface $e) {
                    //We are suppressing field setting exceptions
                }
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Every getter and accessor will be applied/constructed if filter argument set to true.
     *
     * @param bool $filter
     *
     * @throws AccessException
     */
    public function getFields(bool $filter = true): array
    {
        $result = [];
        foreach ($this->fields as $name => $_) {
            $result[$name] = $this->getField($name, null, $filter);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getField($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->setField($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->__unset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->getFields());
    }

    /**
     * AccessorInterface dependency.
     */
    public function setValue($data)
    {
        return $this->setFields($data);
    }

    /**
     * Pack entity fields into plain array.
     *
     *
     * @throws AccessException
     */
    public function getValue(): array
    {
        $result = [];
        foreach ($this->fields as $field => $value) {
            if ($value instanceof ValueInterface) {
                $result[$field] = $value->getValue();
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
    }

    /**
     * Alias for packFields.
     */
    public function toArray(): array
    {
        return $this->getValue();
    }

    /**
     * By default use publicFields to be json serialized.
     */
    public function jsonSerialize(): array
    {
        return $this->getValue();
    }

    /**
     * @return int[]|string[]
     *
     * @psalm-return list<array-key>
     */
    protected function getKeys(): array
    {
        return \array_keys($this->fields);
    }

    /**
     * Reset every field value.
     */
    protected function flushFields(): void
    {
        $this->fields = [];
    }

    /**
     * Check if field is fillable.
     *
     *
     */
    abstract protected function isFillable(string $field): bool;

    /**
     * Get mutator associated with given field.
     *
     * @param string $type See MUTATOR_* constants
     * @return mixed
     */
    abstract protected function getMutator(string $field, string $type);

    /**
     * Nullable fields would not require automatic accessor creation.
     *
     * @return false
     */
    protected function isNullable(string $field): bool
    {
        return false;
    }

    /**
     * Create instance of field accessor.
     *
     * @param mixed|string $type    Might be entity implementation specific.
     * @param mixed        $value
     * @param array        $context Custom accessor context.
     * @return ValueInterface|null
     *
     * @throws AccessException
     * @throws EntityException
     */
    protected function createValue(
        $type,
        string $name,
        $value,
        array $context = []
    ): ValueInterface {
        if (!is_string($type) || !class_exists($type)) {
            throw new EntityException(
                "Unable to create accessor for field `{$name}` in " . static::class
            );
        }

        // field as a context, this is the default convention
        return new $type($value, $context + ['field' => $name, 'entity' => $this]);
    }

    /**
     * Get value thought associated mutator.
     *
     * @param mixed  $value
     * @return mixed
     */
    private function getMutated(string $name, bool $filter, $value)
    {
        $getter = $this->getMutator($name, ModelSchema::MUTATOR_GETTER);

        if ($filter && !empty($getter)) {
            try {
                return call_user_func($getter, $value);
            } catch (\Exception $e) {
                //Trying to filter null value, every filter must support it
                return call_user_func($getter, null);
            }
        }

        return $value;
    }

    /**
     * Set value thought associated mutator.
     *
     * @param mixed  $value
     */
    private function setMutated(string $name, $value): void
    {
        $setter = $this->getMutator($name, ModelSchema::MUTATOR_SETTER);

        if (!empty($setter)) {
            try {
                $this->fields[$name] = call_user_func($setter, $value);
            } catch (\Exception $e) {
                //Exceptional situation, we are choosing to keep original field value
            }
        } else {
            $this->fields[$name] = $value;
        }
    }

    /**
     * Set value in/thought associated accessor.
     *
     * @param string|array $type Accessor definition (implementation specific).
     * @param mixed        $value
     */
    private function thoughValue($type, string $name, $value): void
    {
        if (array_key_exists($name, $this->fields)) {
            $field = $this->fields[$name];
        } else {
            $field = null;
        }

        if (empty($field) || !($field instanceof ValueInterface)) {
            //New field representation
            $field = $this->createValue($type, $name, $value);

            //Save accessor with other fields
            $this->fields[$name] = $field;
        }

        //Letting accessor to set value
        $field->setValue($value);
    }
}

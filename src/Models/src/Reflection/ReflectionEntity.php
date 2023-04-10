<?php

declare(strict_types=1);

namespace Spiral\Models\Reflection;

use Spiral\Models\AbstractEntity;
use Spiral\Models\SchematicEntity;

/**
 * Provides ability to generate entity schema based on given entity class and default property
 * values, support value inheritance!
 *
 * @method bool isAbstract()
 * @method string getName()
 * @method string getShortName()
 * @method bool isSubclassOf($class)
 * @method bool hasConstant($name)
 * @method mixed getConstant($name)
 * @method \ReflectionMethod[] getMethods()
 * @method \ReflectionClass|null getParentClass()
 */
class ReflectionEntity
{
    /**
     * Required to validly merge parent and children attributes.
     */
    protected const BASE_CLASS = AbstractEntity::class;

    /**
     * Accessors and filters.
     */
    private const MUTATOR_GETTER   = 'getter';
    private const MUTATOR_SETTER   = 'setter';
    private const MUTATOR_ACCESSOR = 'accessor';

    /** @internal */
    private array $propertyCache = [];
    private \ReflectionClass $reflection;

    /**
     * Only support SchematicEntity classes!
     */
    public function __construct(string $class)
    {
        $this->reflection = new \ReflectionClass($class);
    }

    /**
     * Bypassing call to reflection.
     */
    public function __call(string $name, array $arguments): mixed
    {
        return \call_user_func_array([$this->reflection, $name], $arguments);
    }


    /**
     * Cloning and flushing cache.
     */
    public function __clone()
    {
        $this->propertyCache = [];
    }

    public function getReflection(): \ReflectionClass
    {
        return $this->reflection;
    }

    public function getSecured(): mixed
    {
        if ($this->getProperty('secured', true) === '*') {
            return $this->getProperty('secured', true);
        }

        return \array_unique((array)$this->getProperty('secured', true));
    }

    public function getFillable(): array
    {
        return \array_unique((array)$this->getProperty('fillable', true));
    }

    public function getSetters(): array
    {
        return $this->getMutators()[self::MUTATOR_SETTER];
    }

    public function getGetters(): array
    {
        return $this->getMutators()[self::MUTATOR_GETTER];
    }

    public function getAccessors(): array
    {
        return $this->getMutators()[self::MUTATOR_ACCESSOR];
    }

    /**
     * Get methods declared in current class and exclude methods declared in parents.
     *
     * @return \ReflectionMethod[]
     */
    public function declaredMethods(): array
    {
        $methods = [];
        foreach ($this->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() != $this->getName()) {
                continue;
            }

            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * Entity schema.
     */
    public function getSchema(): array
    {
        //Default property to store schema
        return (array)$this->getProperty('schema', true);
    }

    /**
     * Model mutators grouped by their type.
     */
    public function getMutators(): array
    {
        $mutators = [
            self::MUTATOR_GETTER   => [],
            self::MUTATOR_SETTER   => [],
            self::MUTATOR_ACCESSOR => [],
        ];

        foreach ((array)$this->getProperty('getters', true) as $field => $filter) {
            $mutators[self::MUTATOR_GETTER][$field] = $filter;
        }

        foreach ((array)$this->getProperty('setters', true) as $field => $filter) {
            $mutators[self::MUTATOR_SETTER][$field] = $filter;
        }

        foreach ((array)$this->getProperty('accessors', true) as $field => $filter) {
            $mutators[self::MUTATOR_ACCESSOR][$field] = $filter;
        }

        return $mutators;
    }

    /**
     * Read default model property value, will read "protected" and "private" properties. Method
     * raises entity event "describe" to allow it traits modify needed values.
     *
     * @param string $property Property name.
     * @param bool   $merge    If true value will be merged with all parent declarations.
     */
    public function getProperty(string $property, bool $merge = false): mixed
    {
        if (isset($this->propertyCache[$property])) {
            //Property merging and trait events are pretty slow
            return $this->propertyCache[$property];
        }

        $properties = $this->reflection->getDefaultProperties();
        $constants = $this->reflection->getConstants();

        if (isset($properties[$property])) {
            //Read from default value
            $value = $properties[$property];
        } elseif (isset($constants[\strtoupper($property)])) {
            //Read from a constant
            $value = $constants[\strtoupper($property)];
        } else {
            return null;
        }

        //Merge with parent value requested
        if ($merge && \is_array($value) && !empty($parent = $this->parentReflection())) {
            $parentValue = $parent->getProperty($property, $merge);

            if (\is_array($parentValue)) {
                //Class values prior to parent values
                $value = \array_merge($parentValue, $value);
            }
        }

        /** @psalm-suppress TypeDoesNotContainType https://github.com/vimeo/psalm/issues/9489 */
        if (!$this->reflection->isSubclassOf(SchematicEntity::class)) {
            return $value;
        }

        //To let traits apply schema changes
        return $this->propertyCache[$property] = $value;
    }

    /**
     * Parent entity schema
     */
    public function parentReflection(): ?self
    {
        $parentClass = $this->reflection->getParentClass();

        if (!empty($parentClass) && $parentClass->getName() != static::BASE_CLASS) {
            $parent = clone $this;
            $parent->reflection = $this->getParentClass();

            return $parent;
        }

        return null;
    }
}

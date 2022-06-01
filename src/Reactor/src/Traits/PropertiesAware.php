<?php

declare(strict_types=1);

namespace Spiral\Reactor\Traits;

use Nette\PhpGenerator\Property as NetteProperty;
use Spiral\Reactor\Aggregator\Properties;
use Spiral\Reactor\Partial\Property;

/**
 * @internal
 */
trait PropertiesAware
{
    public function setProperties(Properties $properties): static
    {
        $this->element->setProperties(\array_map(
            static fn (Property $property) => $property->getElement(),
            \iterator_to_array($properties)
        ));

        return $this;
    }

    public function getProperties(): Properties
    {
        return new Properties(\array_map(
            static fn (NetteProperty $property) => Property::fromElement($property),
            $this->element->getProperties()
        ));
    }

    public function getProperty(string $name): Property
    {
        return $this->getProperties()->get($name);
    }

    /** @param string $name without $ */
    public function addProperty(string $name, mixed $value = null): Property
    {
        if (\func_num_args() > 1) {
            return Property::fromElement($this->element->addProperty($name, $value));
        }

        return Property::fromElement($this->element->addProperty($name));
    }

    /** @param string $name without $ */
    public function removeProperty(string $name): static
    {
        $this->element->removeProperty($name);

        return $this;
    }

    public function hasProperty(string $name): bool
    {
        return $this->element->hasProperty($name);
    }
}

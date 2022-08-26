<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

/**
 * @internal
 */
trait DestructorTrait
{
    public function destruct(): void
    {
        $class = new \ReflectionClass($this);
        foreach ($class->getProperties() as $property) {
            $name = $property->getName();
            if (!isset($this->$name)) {
                continue;
            }
            $value = $this->$name;
            unset($this->$name);
            if (\is_object($value) && \method_exists($value, 'destruct')) {
                $value->destruct();
            }
        }
    }
}

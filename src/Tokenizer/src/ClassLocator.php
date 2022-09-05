<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Spiral\Tokenizer\Exception\LocatorException;
use Spiral\Tokenizer\Traits\TargetTrait;

/**
 * Can locate classes in a specified directory.
 */
final class ClassLocator extends AbstractLocator implements ClassesInterface
{
    use TargetTrait;

    public function getClasses(object|string|null $target = null): array
    {
        if (!empty($target) && (\is_object($target) || \is_string($target))) {
            $target = new \ReflectionClass($target);
        }

        $result = [];
        foreach ($this->availableClasses() as $class) {
            try {
                $reflection = $this->classReflection($class);
            } catch (LocatorException) {
                //Ignoring
                continue;
            }

            if (!$this->isTargeted($reflection, $target) || $reflection->isInterface()) {
                continue;
            }

            $result[$reflection->getName()] = $reflection;
        }

        return $result;
    }

    /**
     * Check if given class targeted by locator.
     *
     * @param \ReflectionClass|null $target
     */
    protected function isTargeted(\ReflectionClass $class, \ReflectionClass $target = null): bool
    {
        if (empty($target)) {
            return true;
        }

        if (!$target->isTrait()) {
            //Target is interface or class
            return $class->isSubclassOf($target) || $class->getName() === $target->getName();
        }

        // Checking using traits
        return \in_array($target->getName(), $this->fetchTraits($class->getName()));
    }

    /**
     * Classes available in finder scope.
     */
    protected function availableClasses(): array
    {
        $classes = [];

        foreach ($this->availableReflections() as $reflection) {
            $classes = \array_merge($classes, $reflection->getClasses());
        }

        return $classes;
    }
}

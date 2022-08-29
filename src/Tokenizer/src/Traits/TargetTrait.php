<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Traits;

trait TargetTrait
{
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

        //Checking using traits
        return \in_array($target->getName(), $this->fetchTraits($class->getName()));
    }

    /**
     * Get every class trait (including traits used in parents).
     *
     * @return string[]
     *
     * @psalm-return array<string, string>
     */
    protected function fetchTraits(string $class): array
    {
        $traits = [];

        do {
            $traits = \array_merge(\class_uses($class), $traits);
            $class = \get_parent_class($class);
        } while ($class !== false);

        //Traits from traits
        foreach (\array_flip($traits) as $trait) {
            $traits = \array_merge(\class_uses($trait), $traits);
        }

        return \array_unique($traits);
    }
}

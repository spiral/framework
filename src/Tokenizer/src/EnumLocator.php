<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Spiral\Tokenizer\Exception\LocatorException;

/**
 * Can locate enums in a specified directory.
 */
final class EnumLocator extends AbstractLocator implements EnumsInterface
{
    public const INJECTOR = EnumLocatorInjector::class;

    public function getEnums(object|string|null $target = null): array
    {
        if (!empty($target)) {
            $target = new \ReflectionClass($target);
        }

        $result = [];
        foreach ($this->availableEnums() as $enum) {
            try {
                $reflection = $this->enumReflection($enum);
            } catch (LocatorException $e) {
                if ($this->debug) {
                    throw $e;
                }

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
     * Enums available in finder scope.
     *
     * @return class-string[]
     */
    protected function availableEnums(): array
    {
        $enums = [];

        foreach ($this->availableReflections() as $reflection) {
            $enums = \array_merge($enums, $reflection->getEnums());
        }

        return $enums;
    }

    /**
     * Check if given enum targeted by locator.
     *
     * @param \ReflectionClass|null $target
     */
    protected function isTargeted(\ReflectionEnum $enum, \ReflectionClass $target = null): bool
    {
        if ($target === null) {
            return true;
        }

        if (!$target->isTrait()) {
            //Target is interface or class
            /** @psalm-suppress RedundantCondition https://github.com/vimeo/psalm/issues/9489 */
            return $enum->isSubclassOf($target) || $enum->getName() === $target->getName();
        }

        // Checking using traits
        return \in_array($target->getName(), $this->fetchTraits($enum->getName()));
    }
}

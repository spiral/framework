<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Spiral\Tokenizer\Exception\LocatorException;

/**
 * Can locate classes in a specified directory.
 */
final class InterfaceLocator extends AbstractLocator implements InterfacesInterface
{
    public const INJECTOR = InterfaceLocatorInjector::class;

    public function getInterfaces(string|null $target = null): array
    {
        if (!empty($target)) {
            $target = new \ReflectionClass($target);
        }

        $result = [];
        foreach ($this->availableInterfaces() as $interface) {
            try {
                $reflection = $this->classReflection($interface);
            } catch (LocatorException $e) {
                if ($this->debug) {
                    throw $e;
                }

                //Ignoring
                continue;
            }

            if (!$this->isTargeted($reflection, $target)) {
                continue;
            }

            $result[$reflection->getName()] = $reflection;
        }

        return $result;
    }

    /**
     * Classes available in finder scope.
     *
     * @return class-string[]
     */
    protected function availableInterfaces(): array
    {
        $interfaces = [];

        foreach ($this->availableReflections() as $reflection) {
            $interfaces = \array_merge($interfaces, $reflection->getInterfaces());
        }

        return $interfaces;
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

        return $class->isSubclassOf($target) || $class->getName() === $target->getName();
    }
}

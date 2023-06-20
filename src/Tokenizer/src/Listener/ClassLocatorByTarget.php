<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Tokenizer\Attribute\AbstractTarget;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ScopedClassesInterface;

/**
 * @internal
 */
final class ClassLocatorByTarget
{
    public function __construct(
        private readonly ClassesInterface $classes,
        private readonly ScopedClassesInterface $scopedClasses,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getClasses(AbstractTarget $target): array
    {
        return \iterator_to_array(
            $target->filter(
                $this->findClasses($target),
            ),
        );
    }

    /**
     * @return \ReflectionClass[]
     */
    private function findClasses(AbstractTarget $target): array
    {
        $scope = $target->getScope();

        // If scope for listener attribute is defined, we should use scoped class locator
        return $scope !== null
            ? $this->scopedClasses->getScopedClasses($scope)
            : $this->classes->getClasses();
    }
}

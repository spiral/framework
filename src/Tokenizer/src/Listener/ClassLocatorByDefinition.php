<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Tokenizer\Attribute\ListenerDefinitionInterface;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\Traits\TargetTrait;

/**
 * @internal
 */
final class ClassLocatorByDefinition
{
    use TargetTrait;

    public function __construct(
        private readonly ClassesInterface $classes,
        private readonly ScopedClassesInterface $scopedClasses,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getClasses(ListenerDefinitionInterface $definition): array
    {
        return \iterator_to_array(
            $definition->filter(
                $this->findClasses($definition),
            ),
        );
    }

    /**
     * @return \ReflectionClass[]
     */
    private function findClasses(ListenerDefinitionInterface $definition): array
    {
        $scope = $definition->getScope();

        // If scope for listener attribute is defined, we should use scoped class locator
        return $scope !== null
            ? $this->scopedClasses->getScopedClasses($scope)
            : $this->classes->getClasses();
    }
}

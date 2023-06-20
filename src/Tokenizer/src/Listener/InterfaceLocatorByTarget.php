<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Tokenizer\Attribute\AbstractTarget;
use Spiral\Tokenizer\InterfacesInterface;
use Spiral\Tokenizer\ScopedInterfacesInterface;

/**
 * @internal
 */
final class InterfaceLocatorByTarget
{
    public function __construct(
        private readonly InterfacesInterface $interfaces,
        private readonly ScopedInterfacesInterface $scopedInterfaces,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getInterfaces(AbstractTarget $target): array
    {
        return \iterator_to_array(
            $target->filter(
                $this->findInterfaces($target),
            ),
        );
    }

    /**
     * @return \ReflectionClass[]
     */
    private function findInterfaces(AbstractTarget $target): array
    {
        $scope = $target->getScope();

        // If scope for listener attribute is defined, we should use scoped class locator
        return $scope !== null
            ? $this->scopedInterfaces->getScopedInterfaces($scope)
            : $this->interfaces->getInterfaces();
    }
}

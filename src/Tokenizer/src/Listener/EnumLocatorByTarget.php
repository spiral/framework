<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Tokenizer\Attribute\AbstractTarget;
use Spiral\Tokenizer\EnumsInterface;
use Spiral\Tokenizer\ScopedEnumsInterface;

/**
 * @internal
 */
final class EnumLocatorByTarget
{
    public function __construct(
        private readonly EnumsInterface $enums,
        private readonly ScopedEnumsInterface $scopedEnums,
    ) {
    }

    /**
     * @return class-string[]
     */
    public function getEnums(AbstractTarget $target): array
    {
        return \iterator_to_array(
            $target->filter(
                $this->findEnums($target),
            ),
        );
    }

    /**
     * @return \ReflectionEnum[]
     */
    private function findEnums(AbstractTarget $target): array
    {
        $scope = $target->getScope();

        // If scope for listener attribute is defined, we should use scoped class locator
        return $scope !== null
            ? $this->scopedEnums->getScopedEnums($scope)
            : $this->enums->getEnums();
    }
}

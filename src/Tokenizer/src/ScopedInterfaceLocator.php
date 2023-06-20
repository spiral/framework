<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

final class ScopedInterfaceLocator implements ScopedInterfacesInterface
{
    public function __construct(
        private readonly Tokenizer $tokenizer
    ) {
    }

    public function getScopedInterfaces(string $scope, string|null $target = null): array
    {
        return $this->tokenizer->scopedInterfaceLocator($scope)->getInterfaces($target);
    }
}

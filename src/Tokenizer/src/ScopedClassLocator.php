<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

final class ScopedClassLocator implements ScopedClassesInterface
{
    public function __construct(
        private readonly Tokenizer $tokenizer
    ) {
    }

    public function getScopedClasses(string $scope, object|string|null $target = null): array
    {
        return $this->tokenizer->scopedClassLocator($scope)->getClasses($target);
    }
}

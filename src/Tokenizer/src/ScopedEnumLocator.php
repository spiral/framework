<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

final class ScopedEnumLocator implements ScopedEnumsInterface
{
    public function __construct(
        private readonly Tokenizer $tokenizer
    ) {
    }

    public function getScopedEnums(string $scope, object|string|null $target = null): array
    {
        return $this->tokenizer->scopedEnumLocator($scope)->getEnums($target);
    }
}

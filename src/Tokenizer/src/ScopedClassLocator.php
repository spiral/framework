<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

final class ScopedClassLocator implements ScopedClassesInterface
{
    private Tokenizer $tokenizer;

    public function __construct(Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public function getScopedClasses(string $scope, $target = null): array
    {
        return $this->tokenizer->scopedClassLocator($scope)->getClasses($target);
    }
}

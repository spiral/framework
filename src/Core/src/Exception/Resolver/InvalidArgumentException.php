<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

final class InvalidArgumentException extends ValidationException
{
    protected function getValidationMessage(
        \ReflectionFunctionAbstract $reflection,
        string $parameter,
    ): string {
        $pattern = "Invalid argument value type for the `$parameter` parameter when validating arguments for `%s`.";
        return $this->renderFunctionAndParameter($reflection, $pattern);
    }
}

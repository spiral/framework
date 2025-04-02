<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

final class UnknownParameterException extends ValidationException
{
    protected function getValidationMessage(
        \ReflectionFunctionAbstract $reflection,
        string $parameter,
    ): string {
        $pattern = "Unknown named parameter `{$parameter}` `%s` %s.";
        return $this->renderFunctionAndParameter($reflection, $pattern);
    }
}

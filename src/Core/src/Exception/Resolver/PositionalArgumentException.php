<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

final class PositionalArgumentException extends ValidationException
{
    protected function getValidationMessage(
        \ReflectionFunctionAbstract $reflection,
        string $parameter,
    ): string {
        $pattern = 'Cannot use positional argument after named argument `%s` %s.';
        return $this->renderFunctionAndParameter($reflection, $pattern);
    }

    public function getParameter(): string
    {
        return '#' . $this->parameter;
    }
}

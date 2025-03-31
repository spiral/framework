<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

use Spiral\Core\Exception\Traits\ClosureRendererTrait;

final class MissingRequiredArgumentException extends ValidationException
{
    use ClosureRendererTrait;

    protected function getValidationMessage(
        \ReflectionFunctionAbstract $reflection,
        string $parameter,
    ): string {
        $pattern = "Missing required argument for the `{$parameter}` parameter for `%s` %s.";
        return $this->renderFunctionAndParameter($reflection, $pattern);
    }
}

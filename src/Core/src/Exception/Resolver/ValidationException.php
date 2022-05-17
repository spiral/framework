<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

abstract class ValidationException extends ResolvingException
{
    abstract public function getParameter(): string;
}

<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

final class ArgumentException extends ResolvingException
{
    public function __construct(\ReflectionFunctionAbstract $reflection, string $parameter)
    {
        $pattern = "Unable to resolve required argument `{$parameter}` when resolving `%s` %s.";
        parent::__construct($this->RenderFunctionAndParameter($reflection, $pattern));
    }
}

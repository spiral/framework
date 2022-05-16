<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

use Spiral\Core\Exception\Traits\ClosureRendererTrait;

final class UnsupportedTypeException extends ResolvingException
{
    use ClosureRendererTrait;

    public function __construct(\ReflectionFunctionAbstract $reflection, string $parameter)
    {
        $pattern = "Can not resolve unsupported type of the `{$parameter}` parameter in `%s` %s.";
        parent::__construct($this->renderFunctionAndParameter($reflection, $pattern));
    }
}

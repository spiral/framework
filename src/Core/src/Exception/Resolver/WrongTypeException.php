<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

use TypeError;

final class WrongTypeException extends ResolvingException
{
    public function __construct(\ReflectionFunctionAbstract $reflection, TypeError $error)
    {
        $message = 'An argument resolved with wrong type: ';
        parent::__construct(
            $message . $error->getMessage(),
            $error->getCode(),
            $error
        );
    }
}

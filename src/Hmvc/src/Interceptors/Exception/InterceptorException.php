<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Exception;

class InterceptorException extends \RuntimeException
{
}

\class_alias(InterceptorException::class, \Spiral\Core\Exception\InterceptorException::class);

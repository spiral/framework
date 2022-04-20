<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

final class UnsupportedTypeException extends ResolvingException
{
    protected const EXCEPTION_MESSAGE = 'Can not resolve unsupported type of the `%s` parameter in `%s` %s.';
}

<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Resolver;

final class ArgumentNotFoundException extends ResolvingException
{
    protected const EXCEPTION_MESSAGE = 'Missing required argument `%s` when resolving `%s` %s.';
}

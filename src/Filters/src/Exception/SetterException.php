<?php

declare(strict_types=1);

namespace Spiral\Filters\Exception;

class SetterException extends FilterException
{
    public function __construct(\Throwable $previous = null, ?string $message = null)
    {
        parent::__construct(
            message: $message ?? 'Unable to set value. The given data was invalid.',
            previous: $previous,
        );
    }
}

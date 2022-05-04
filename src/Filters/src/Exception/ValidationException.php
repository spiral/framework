<?php

declare(strict_types=1);

namespace Spiral\Filters\Exception;

class ValidationException extends FilterException
{
    public function __construct(
        public readonly array $errors,
        public readonly mixed $context = null
    ) {
        parent::__construct('The given data was invalid.', 422);
    }
}

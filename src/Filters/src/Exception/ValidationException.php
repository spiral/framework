<?php

declare(strict_types=1);

namespace Spiral\Filters\Exception;

class ValidationException extends FilterException
{
    public function __construct(
        private readonly array $errors,
        private readonly mixed $context = null
    ) {
        parent::__construct('The given data was invalid.', 422);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getContext(): mixed
    {
        return $this->context;
    }
}

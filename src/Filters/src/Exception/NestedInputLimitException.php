<?php

declare(strict_types=1);

namespace Spiral\Filters\Exception;

/**
 * The input of nested filters exceeds a limit from {@see \Spiral\Filters\Config\FiltersConfig}.
 *
 * Filter mappers do not turn it into a field error of the parent filter: they rethrow it with the path
 * to the failed element, so building the root filter stops at the first violation.
 */
final class NestedInputLimitException extends ValidationException
{
    public function __construct(
        public readonly string $reason,
        array $errors = [],
        mixed $context = null,
    ) {
        parent::__construct($errors === [] ? [$reason] : $errors, $context);
        $this->message = $reason;
    }

    /**
     * Nests the errors under the field (and the array key) of the parent filter.
     */
    public function under(string $field, int|string|null $key = null): self
    {
        $errors = $key === null ? [$field => $this->errors] : [$field => [$key => $this->errors]];

        return new self($this->reason, $errors, $this->context);
    }
}

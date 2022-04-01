<?php

declare(strict_types=1);

namespace Spiral\Domain\Exception;

use Spiral\Core\Exception\ControllerException;
use Spiral\Filters\FilterInterface;

/**
 * Triggered when provided filter is not valid. Must be handled via custom middleware.
 */
final class InvalidFilterException extends ControllerException
{
    private array $errors = [];

    /**
     * FilterException constructor.
     */
    public function __construct(FilterInterface $filter)
    {
        $this->errors = $filter->getErrors();

        parent::__construct(\sprintf('Invalid `%s`', $filter::class), self::BAD_ARGUMENT);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Domain\Exception;

use Spiral\Core\Exception\ControllerException;
use Spiral\Filters\FilterInterface;

/**
 * Triggered when provided filter is not valid. Must be handled via custom middleware.
 */
final class InvalidFilterException extends ControllerException
{
    /** @var array */
    private $errors;

    /**
     * FilterException constructor.
     *
     * @param FilterInterface $filter
     */
    public function __construct(FilterInterface $filter)
    {
        $this->errors = $filter->getErrors();

        parent::__construct(sprintf("Invalid `%s`", get_class($filter)), self::BAD_ARGUMENT);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Spiral\Domain\Exception\InvalidFilterException;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\RenderErrors;

/**
 * @template-implements RenderErrors<FilterInterface>
 */
final class DefaultFilterRenderer implements RenderErrors
{
    public const STRATEGY_JSON_RESPONSE = 1;
    public const STRATEGY_EXCEPTION     = 2;

    private int $strategy;

    public function __construct(int $strategy = self::STRATEGY_JSON_RESPONSE)
    {
        $this->strategy = $strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function render(FilterInterface $filter)
    {
        switch ($this->strategy) {
            case self::STRATEGY_JSON_RESPONSE:
                return [
                    'status' => 400,
                    'errors' => $filter->getErrors(),
                ];
            default:
                throw new InvalidFilterException($filter);
        }
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Domain;

use Spiral\Domain\Exception\InvalidFilterException;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\RenderErrorsInterface;

/**
 * @template-implements RenderErrorsInterface<FilterInterface>
 */
final class DefaultFilterErrorsRendererInterface implements RenderErrorsInterface
{
    private int $strategy;

    public function __construct(int $strategy = FilterInterceptor::STRATEGY_JSON_RESPONSE)
    {
        $this->strategy = $strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function render(FilterInterface $filter)
    {
        switch ($this->strategy) {
            case FilterInterceptor::STRATEGY_JSON_RESPONSE:
                return [
                    'status' => 400,
                    'errors' => $filter->getErrors(),
                ];
            default:
                throw new InvalidFilterException($filter);
        }
    }
}

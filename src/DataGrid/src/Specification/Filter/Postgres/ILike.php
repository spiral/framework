<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter\Postgres;

use Spiral\DataGrid\Specification\Filter\Like;
use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

final class ILike implements FilterInterface
{
    /** @var Like */
    private $like;

    public function __construct(string $expression, $value = null, string $pattern = '%%%s%%')
    {
        $this->like = new Like($expression, $value, $pattern);
    }

    /**
     * @inheritDoc
     */
    public function withValue($value): ?SpecificationInterface
    {
        $filter = clone $this;
        $filter->like = $filter->like->withValue($value);

        return $filter;
    }

    public function getExpression(): string
    {
        return $this->like->getExpression();
    }

    public function getPattern(): string
    {
        return $this->like->getPattern();
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->like->getValue();
    }
}

<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter\Postgres;

use Spiral\DataGrid\Specification\Filter\Like;
use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

final class ILike implements FilterInterface
{
    private readonly Like $like;

    public function __construct(string $expression, mixed $value = null, string $pattern = '%%%s%%')
    {
        $this->like = new Like($expression, $value, $pattern);
    }

    public function withValue(mixed $value): ?SpecificationInterface
    {
        $filter = clone $this;

        return $filter->like->withValue($value);
    }

    public function getExpression(): string
    {
        return $this->like->getExpression();
    }

    public function getPattern(): string
    {
        return $this->like->getPattern();
    }

    public function getValue(): mixed
    {
        return $this->like->getValue();
    }
}

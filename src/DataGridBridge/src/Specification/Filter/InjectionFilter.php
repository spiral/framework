<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use LogicException;
use Spiral\Database\Injection;
use Spiral\DataGrid\Specification\FilterInterface;
use Spiral\DataGrid\SpecificationInterface;

abstract class InjectionFilter implements FilterInterface
{
    protected const INJECTION = '';

    /** @var Between|Expression */
    private $expression;

    public function __construct(SpecificationInterface $expression)
    {
        if (!$expression instanceof Expression && !$expression instanceof Between) {
            throw new LogicException('Only expression filters allowed');
        }

        $this->expression = $expression;
    }

    public static function createFrom(InjectionFilter $injector, SpecificationInterface $expression): InjectionFilter
    {
        $clone = clone $injector;
        $clone->expression = $expression;

        return $clone;
    }

    /**
     * @return SpecificationInterface
     */
    public function getFilter(): SpecificationInterface
    {
        return $this->expression;
    }

    /**
     * @return Injection\FragmentInterface
     */
    public function getInjection(): Injection\FragmentInterface
    {
        $injector = static::INJECTION;
        return new $injector($this->expression->getExpression());
    }

    /**
     * @inheritDoc
     */
    public function withValue($value): ?SpecificationInterface
    {
        $filter = clone $this;
        $filter->expression = $filter->expression->withValue($value);

        return $filter->expression === null ? null : $filter;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->expression->getValue();
    }
}

<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Spiral\Database\Injection\FragmentInterface;
use Spiral\DataGrid\Specification\Sorter\AbstractSorter;
use Spiral\DataGrid\SpecificationInterface;

abstract class InjectionSorter extends AbstractSorter
{
    protected const INJECTION = '';

    /**
     * @var AbstractSorter
     */
    private $expression;

    public function __construct(SpecificationInterface $expression)
    {
        if (!$expression instanceof AbstractSorter) {
            throw new \LogicException('Only sorters allowed');
        }

        $this->expression = $expression;
    }

    /**
     * @return FragmentInterface[]
     */
    public function getInjections(): array
    {
        $injector = static::INJECTION;

        if (!class_exists($injector)) {
            throw new \LogicException(
                sprintf('Class "%s" does not exist', $injector)
            );
        }

        if (!is_subclass_of($injector, FragmentInterface::class)) {
            throw new \LogicException(
                'INJECTION class does not implement FragmentInterface'
            );
        }

        return array_map(
            function (string $expression) use ($injector): FragmentInterface {
                return new $injector($expression);
            },
            $this->expression->getExpressions()
        );
    }

    public function getSorter(): AbstractSorter
    {
        return $this->expression;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): string
    {
        return $this->expression->getValue();
    }
}

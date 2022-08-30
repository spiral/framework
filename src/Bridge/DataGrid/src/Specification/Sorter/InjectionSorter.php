<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Cycle\Database\Injection\FragmentInterface;
use Spiral\DataGrid\SpecificationInterface;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
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

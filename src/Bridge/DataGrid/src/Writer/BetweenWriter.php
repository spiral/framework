<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Writer;

use Spiral\Database\Injection\Parameter;
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Specification\Filter;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\WriterInterface;

class BetweenWriter implements WriterInterface
{
    /** @var bool */
    private $asOriginal;

    /**
     * @param bool $asOriginal
     */
    public function __construct(bool $asOriginal = false)
    {
        $this->asOriginal = $asOriginal;
    }

    /**
     * @inheritDoc
     */
    public function write($source, SpecificationInterface $specification, Compiler $compiler)
    {
        if ($specification instanceof Filter\Between || $specification instanceof Filter\ValueBetween) {
            $filters = $specification->getFilters($this->asOriginal);
            if (count($filters) > 1) {
                return $source->where(static function () use ($compiler, $source, $filters): void {
                    $compiler->compile($source, ...$filters);
                });
            }
        }

        if ($specification instanceof Filter\InjectionFilter) {
            $expression = $specification->getFilter();
            if ($expression instanceof Filter\Between) {
                $filters = $expression->getFilters($this->asOriginal);
                if (count($filters) > 1) {
                    $filters = array_map(
                        static function (SpecificationInterface $filter) use ($specification): Filter\InjectionFilter {
                            return Filter\InjectionFilter::createFrom($specification, $filter);
                        },
                        $filters
                    );

                    return $source->where(
                        static function () use ($compiler, $source, $filters): void {
                            $compiler->compile($source, ...$filters);
                        }
                    );
                }

                return $source->where(
                    $specification->getInjection(),
                    'BETWEEN',
                    ...$specification->getValue()
                );
            }
        }

        if ($specification instanceof Filter\Between) {
            return $source->where(
                $specification->getExpression(),
                'BETWEEN',
                ...$specification->getValue()
            );
        }

        if ($specification instanceof Filter\ValueBetween) {
            return $source->where(
                new Parameter($specification->getValue()),
                'BETWEEN',
                ...$specification->getExpression()
            );
        }

        return null;
    }
}

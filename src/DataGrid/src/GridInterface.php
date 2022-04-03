<?php

declare(strict_types=1);

namespace Spiral\DataGrid;

use IteratorAggregate;

/**
 * Responsible for grid data and specification representation.
 */
interface GridInterface extends IteratorAggregate
{
    public const FILTERS   = 'filters';
    public const SORTERS   = 'sorters';
    public const PAGINATOR = 'paginator';
    public const COUNT     = 'count';

    /**
     * Associate public value with the grid.
     */
    public function withOption(string $name, mixed $value): GridInterface;

    /**
     * Returns associated value.
     */
    public function getOption(string $name): mixed;

    /**
     * Associated input source with the grid view. The source will be iterated using the given mapper.
     */
    public function withSource(iterable $source): GridInterface;

    /**
     * Associate mapping class or function with the grid view.
     * All grid source items will be passed thought this function.
     */
    public function withView(callable $view): GridInterface;
}

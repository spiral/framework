<?php

declare(strict_types=1);

namespace Spiral\DataGrid;

use Spiral\DataGrid\Exception\CompilerException;
use Spiral\DataGrid\Exception\GridViewException;
use Spiral\DataGrid\Input\ArrayInput;
use Spiral\DataGrid\Input\NullInput;
use Spiral\DataGrid\Specification\FilterInterface;

/**
 * Generates grid views based on provided inout source and grid specifications.
 */
class GridFactory implements GridFactoryInterface
{
    public const KEY_FILTER      = 'filter';
    public const KEY_SORT        = 'sort';
    public const KEY_PAGINATE    = 'paginate';
    public const KEY_FETCH_COUNT = 'fetchCount';

    private \Closure $count;
    private InputInterface $defaults;

    public function __construct(
        private Compiler $compiler,
        private InputInterface $input = new NullInput(),
        private GridInterface $view = new Grid()
    ) {
        $this->defaults = new NullInput();
        $this->count = count(...);
    }

    /**
     * Associate new input source with grid generator.
     */
    public function withInput(InputInterface $input): self
    {
        $generator = clone $this;
        $generator->input = $input;

        return $generator;
    }

    /**
     * USe default filter values.
     */
    public function withDefaults(array $data): self
    {
        $generator = clone $this;
        $generator->defaults = new ArrayInput($data);

        return $generator;
    }

    public function withCounter(callable $counter): self
    {
        $generator = clone $this;
        $generator->count = $counter(...);

        return $generator;
    }

    public function create(mixed $source, GridSchema $schema): GridInterface
    {
        $view = clone $this->view;

        ['view' => $view, 'source' => $source] = $this->applyFilters($view, $source, $schema);
        ['view' => $view, 'source' => $source] = $this->applyCounter($view, $source, $schema);
        ['view' => $view, 'source' => $source] = $this->applySorters($view, $source, $schema);
        ['view' => $view, 'source' => $source] = $this->applyPaginator($view, $source, $schema);

        if (!\is_iterable($source)) {
            throw new GridViewException('GridView expects the source to be iterable after all.');
        }

        return $view->withSource($source);
    }

    protected function applyFilters(GridInterface $view, mixed $source, GridSchema $schema): array
    {
        ['source' => $source, 'filters' => $filters] = $this->getFilters($source, $schema);
        $view = $view->withOption(GridInterface::FILTERS, $filters);

        return ['view' => $view, 'source' => $source];
    }

    protected function getFilters(mixed $source, GridSchema $schema): array
    {
        $filters = [];
        foreach ($this->getOptionArray(static::KEY_FILTER) ?? [] as $name => $value) {
            if ($schema->hasFilter($name)) {
                $filter = $schema->getFilter($name)->withValue($value);

                if ($filter !== null) {
                    $source = $this->compiler->compile($source, $filter);
                    $filters[$name] = $filter->getValue();
                }
            }
        }

        return ['source' => $source, 'filters' => $filters];
    }

    protected function applyCounter(GridInterface $view, mixed $source, GridSchema $schema): array
    {
        if (is_countable($source) && $this->getOption(static::KEY_FETCH_COUNT)) {
            $view = $view->withOption(GridInterface::COUNT, ($this->count)($source));
        }

        return ['view' => $view, 'source' => $source];
    }

    protected function applySorters(GridInterface $view, mixed $source, GridSchema $schema): array
    {
        ['source' => $source, 'sorters' => $sorters] = $this->getSorters($source, $schema);
        $view = $view->withOption(GridInterface::SORTERS, $sorters);

        return ['view' => $view, 'source' => $source];
    }

    protected function getSorters(mixed $source, GridSchema $schema): array
    {
        $sorters = [];
        foreach ($this->getOptionArray(static::KEY_SORT) ?? [] as $name => $value) {
            if ($schema->hasSorter($name)) {
                $sorter = $schema->getSorter($name)->withDirection((string) $value);

                if ($sorter !== null) {
                    $source = $this->compiler->compile($source, $sorter);
                    $sorters[$name] = $sorter->getValue();
                }
            }
        }

        return ['source' => $source, 'sorters' => $sorters];
    }

    protected function applyPaginator(GridInterface $view, mixed $source, GridSchema $schema): array
    {
        if ($schema->getPaginator() !== null) {
            ['source' => $source, 'paginator' => $paginator] = $this->getPaginator($source, $schema);
            $view = $view->withOption(GridInterface::PAGINATOR, $paginator);
        }

        return ['view' => $view, 'source' => $source];
    }

    protected function getPaginator(mixed $source, GridSchema $schema): array
    {
        $paginator = $schema->getPaginator();
        if (!$paginator instanceof FilterInterface) {
            throw new CompilerException('Paginator can not be null');
        }

        $withValue = $paginator->withValue($this->getOption(static::KEY_PAGINATE));
        if ($withValue === null) {
            throw new CompilerException('Paginator can not be null');
        }

        return [
            'source'    => $this->compiler->compile($source, $withValue),
            'paginator' => $withValue->getValue(),
        ];
    }

    /**
     * Return array of options for the input. Checks the default input in case of value missing in parent.
     */
    protected function getOptionArray(string $option): array
    {
        $result = $this->getOption($option);
        if (!\is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * Return array of options for the input. Checks the default input in case of value missing in parent.
     */
    protected function getOption(string $option): mixed
    {
        if ($this->input->hasValue($option)) {
            return $this->input->getValue($option);
        }

        return $this->defaults->getValue($option);
    }
}

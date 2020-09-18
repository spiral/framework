<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid;

use Spiral\DataGrid\Exception\CompilerException;
use Spiral\DataGrid\Exception\GridViewException;
use Spiral\DataGrid\Input\ArrayInput;
use Spiral\DataGrid\Input\NullInput;

/**
 * Generates grid views based on provided inout source and grid specifications.
 */
class GridFactory implements GridFactoryInterface
{
    public const KEY_FILTER      = 'filter';
    public const KEY_SORT        = 'sort';
    public const KEY_PAGINATE    = 'paginate';
    public const KEY_FETCH_COUNT = 'fetchCount';

    /** @var callable */
    private $count = 'count';

    /** @var Compiler */
    private $compiler;

    /** @var InputInterface */
    private $input;

    /** @var InputInterface */
    private $defaults;

    /** @var GridInterface */
    private $view;

    /**
     * @param Compiler            $compiler
     * @param InputInterface|null $input
     * @param GridInterface|null  $view
     */
    public function __construct(Compiler $compiler, InputInterface $input = null, GridInterface $view = null)
    {
        $this->compiler = $compiler;
        $this->input = $input ?? new NullInput();
        $this->defaults = new NullInput();
        $this->view = $view ?? new Grid();
    }

    /**
     * Associate new input source with grid generator.
     *
     * @param InputInterface $input
     * @return GridFactory
     */
    public function withInput(InputInterface $input): self
    {
        $generator = clone $this;
        $generator->input = $input;

        return $generator;
    }

    /**
     * USe default filter values.
     *
     * @param array $data
     * @return $this
     */
    public function withDefaults(array $data): self
    {
        $generator = clone $this;
        $generator->defaults = new ArrayInput($data);

        return $generator;
    }

    /**
     * @param callable $counter
     * @return $this
     */
    public function withCounter(callable $counter): self
    {
        $generator = clone $this;
        $generator->count = $counter;

        return $generator;
    }

    /**
     * @inheritDoc
     */
    public function create($source, GridSchema $schema): GridInterface
    {
        $view = clone $this->view;

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
        $view = $view->withOption(GridInterface::FILTERS, $filters);

        if (is_countable($source) && $this->getOption(static::KEY_FETCH_COUNT)) {
            $view = $view->withOption(GridInterface::COUNT, ($this->count)($source));
        }

        $sorters = [];
        foreach ($this->getOptionArray(static::KEY_SORT) ?? [] as $name => $value) {
            if ($schema->hasSorter($name)) {
                $sorter = $schema->getSorter($name)->withDirection($value);

                if ($sorter !== null) {
                    $source = $this->compiler->compile($source, $sorter);
                    $sorters[$name] = $sorter->getValue();
                }
            }
        }
        $view = $view->withOption(GridInterface::SORTERS, $sorters);

        if ($schema->getPaginator() !== null) {
            $paginator = $schema->getPaginator()->withValue($this->getOption(static::KEY_PAGINATE));
            if ($paginator === null) {
                throw new CompilerException('The paginator can not be null');
            }

            $source = $this->compiler->compile($source, $paginator);
            $view = $view->withOption(GridInterface::PAGINATOR, $paginator->getValue());
        }

        if (!is_iterable($source)) {
            throw new GridViewException('GridView expects the source to be iterable after all.');
        }

        return $view->withSource($source);
    }

    /**
     * Return array of options for the input. Checks the default input in case of value missing in parent.
     *
     * @param string $option
     * @return array
     */
    private function getOptionArray(string $option): array
    {
        $result = $this->getOption($option);
        if (!is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * Return array of options for the input. Checks the default input in case of value missing in parent.
     *
     * @param string $option
     * @return mixed
     */
    private function getOption(string $option)
    {
        if ($this->input->hasValue($option)) {
            return $this->input->getValue($option);
        }

        return $this->defaults->getValue($option);
    }
}

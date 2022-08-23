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

use Generator;
use Spiral\DataGrid\Exception\GridViewException;

/**
 * Carries information about compiled selection results and their specifications.
 */
class Grid implements GridInterface
{
    /** @var array */
    protected $options = [];

    /** @var iterable */
    protected $source;

    /** @var callable */
    protected $mapper;

    public function getIterator(): Generator
    {
        if ($this->source === null) {
            throw new GridViewException('GridView does not have associated data source');
        }

        foreach ($this->source as $key => $item) {
            if ($this->mapper === null) {
                yield $key => $item;
                continue;
            }

            yield $key => ($this->mapper)($item);
        }
    }

    /**
     * @inheritDoc
     */
    public function withOption(string $name, $value): GridInterface
    {
        $grid = clone $this;
        $grid->options[$name] = $value;

        return $grid;
    }

    /**
     * @inheritDoc
     */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function withSource(iterable $source): GridInterface
    {
        $grid = clone $this;
        $grid->source = $source;

        return $grid;
    }

    public function getSource(): ?iterable
    {
        return $this->source;
    }

    /**
     * @inheritDoc
     */
    public function withView(callable $view): GridInterface
    {
        $grid = clone $this;
        $grid->mapper = $view;

        return $grid;
    }
}

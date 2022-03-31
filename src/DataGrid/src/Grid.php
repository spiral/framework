<?php

declare(strict_types=1);

namespace Spiral\DataGrid;

use Spiral\DataGrid\Exception\GridViewException;

/**
 * Carries information about compiled selection results and their specifications.
 */
class Grid implements GridInterface
{
    private array $options = [];
    private ?iterable $source = null;
    /** @var callable|null */
    private mixed $mapper = null;

    public function getIterator(): \Generator
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

    public function withOption(string $name, mixed $value): GridInterface
    {
        $grid = clone $this;
        $grid->options[$name] = $value;

        return $grid;
    }

    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

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

    public function withView(callable $view): GridInterface
    {
        $grid = clone $this;
        $grid->mapper = $view;

        return $grid;
    }
}

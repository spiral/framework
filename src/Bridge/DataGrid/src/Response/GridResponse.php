<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Response;

use Spiral\DataGrid\GridInterface;

final class GridResponse implements \JsonSerializable, GridResponseInterface
{
    /** @var GridInterface */
    private $grid = null;

    /** @var array */
    private $data = null;

    /** @var array */
    private $options = [];

    /**
     * @param GridInterface $grid
     * @param array         $options
     * @return GridResponseInterface
     * @throws \Exception
     */
    public function withGrid(GridInterface $grid, array $options = []): GridResponseInterface
    {
        $response = clone $this;
        $response->grid = $grid;
        $response->options = $options;
        $response->data = iterator_to_array($grid->getIterator());

        return $response;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        if ($this->grid === null) {
            return [
                'status' => 500,
                'error'  => 'missing-grid-source',
            ];
        }

        $response = [
            'status'                          => $this->option('status', 200),
            $this->option('property', 'data') => $this->data,
        ];

        if ($this->grid->getOption(GridInterface::PAGINATOR) !== null) {
            $response['pagination'] = $this->grid->getOption(GridInterface::PAGINATOR);
        }

        if (isset($response['pagination']) && $this->grid->getOption(GridInterface::COUNT) !== null) {
            $response['pagination']['count'] = $this->grid->getOption(GridInterface::COUNT);
        }

        return $response;
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    private function option(string $name, $default)
    {
        return $this->options[$name] ?? $default;
    }
}

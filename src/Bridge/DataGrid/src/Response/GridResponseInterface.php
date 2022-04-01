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

interface GridResponseInterface
{
    /**
     * Create response configured with Grid result.
     *
     * @param GridInterface $grid
     * @param array         $options
     * @return self
     */
    public function withGrid(GridInterface $grid, array $options = []): self;
}

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

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
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

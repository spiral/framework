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

interface GridFactoryInterface
{
    /**
     * Generate new grid view using given source and data schema.
     *
     * @param mixed      $source
     */
    public function create($source, GridSchema $schema): GridInterface;
}

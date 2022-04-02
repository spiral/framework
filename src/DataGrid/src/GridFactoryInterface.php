<?php

declare(strict_types=1);

namespace Spiral\DataGrid;

interface GridFactoryInterface
{
    /**
     * Generate new grid view using given source and data schema.
     */
    public function create(mixed $source, GridSchema $schema): GridInterface;
}

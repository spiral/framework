<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Config;

use Spiral\Core\InjectableConfig;

/**
 * Configuration for data grid bridge writers.
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class GridConfig extends InjectableConfig
{
    public const CONFIG = 'dataGrid';

    /** @var array */
    protected $config = [
        'writers' => [],
    ];

    /**
     * @return array
     */
    public function getWriters(): array
    {
        return $this->config['writers'];
    }
}

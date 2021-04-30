<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Builder\Adapter;

use League\Flysystem\FilesystemAdapter;

interface AdapterBuilderInterface
{
    /**
     * Build adapter with minimal required params
     *
     * @return FilesystemAdapter
     */
    public function buildSimple(): FilesystemAdapter;

    /**
     * Build adapter with some of additional configurable params
     *
     * @return FilesystemAdapter
     */
    public function buildAdvanced(): FilesystemAdapter;
}

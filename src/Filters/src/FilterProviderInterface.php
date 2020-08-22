<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Filters\Exception\FilterException;

/**
 * Creates filters on demand based on a given name and input.
 */
interface FilterProviderInterface
{
    /**
     * @param string         $name
     * @param InputInterface $input
     * @return FilterInterface
     *
     * @throws FilterException
     */
    public function createFilter(string $name, InputInterface $input): FilterInterface;
}

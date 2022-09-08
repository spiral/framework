<?php

declare(strict_types=1);

namespace Spiral\Filters\Model;

use Spiral\Filters\Exception\FilterException;
use Spiral\Filters\InputInterface;

/**
 * Creates filters on demand based on a given name and input.
 */
interface FilterProviderInterface
{
    /**
     * @throws FilterException
     */
    public function createFilter(string $name, InputInterface $input): FilterInterface;
}

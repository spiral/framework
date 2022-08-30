<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\Method;

/**
 * Method aggregation
 *
 * @extends Aggregator<Method>
 */
final class Methods extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Method::class], $constants);
    }
}

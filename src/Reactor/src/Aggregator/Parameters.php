<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\Parameter;

/**
 * Constants aggregation
 *
 * @implements Aggregator<Parameter>
 */
final class Parameters extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Parameter::class], $constants);
    }
}

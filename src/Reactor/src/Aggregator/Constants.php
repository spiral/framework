<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\Partial\Constant;

/**
 * Constants aggregation.
 *
 * @implements Aggregator<Constant>
 */
final class Constants extends Aggregator
{
    public function __construct(array $constants)
    {
        parent::__construct([Constant::class], $constants);
    }
}

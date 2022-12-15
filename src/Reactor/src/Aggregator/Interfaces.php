<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\InterfaceDeclaration;

/**
 * Interfaces aggregation.
 *
 * @extends Aggregator<InterfaceDeclaration>
 */
final class Interfaces extends Aggregator
{
    public function __construct(array $interfaces)
    {
        parent::__construct([InterfaceDeclaration::class], $interfaces);
    }
}

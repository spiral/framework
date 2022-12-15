<?php

declare(strict_types=1);

namespace Spiral\Reactor\Aggregator;

use Spiral\Reactor\Aggregator;
use Spiral\Reactor\EnumDeclaration;

/**
 * Enums aggregation.
 *
 * @extends Aggregator<EnumDeclaration>
 */
final class Enums extends Aggregator
{
    public function __construct(array $enums)
    {
        parent::__construct([EnumDeclaration::class], $enums);
    }
}

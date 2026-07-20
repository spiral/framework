<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Rules\Fixtures;

use Spiral\Security\Rule\CompositeRule;

/**
 * Class OneCompositeRule
 *
 * @package Spiral\Tests\Security\Actors
 */
class OneCompositeRule extends CompositeRule
{
    public const RULES = ['test.create', 'test.update', 'test.delete'];
    public const BEHAVIOUR = self::AT_LEAST_ONE;
}

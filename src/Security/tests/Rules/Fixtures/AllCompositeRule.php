<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Rules\Fixtures;

use Spiral\Security\Rule\CompositeRule;

/**
 * Class AllCompositeRule
 *
 * @package Spiral\Tests\Security\Rules\Fixtures
 */
class AllCompositeRule extends CompositeRule
{
    public const RULES = ['test.create', 'test.update', 'test.delete'];
    public const BEHAVIOUR = self::ALL;
}

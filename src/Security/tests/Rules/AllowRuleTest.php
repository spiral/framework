<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Rules;

use PHPUnit\Framework\TestCase;
use Spiral\Security\ActorInterface;
use Spiral\Security\RuleInterface;
use Spiral\Security\Rule\AllowRule;

/**
 * Class AllowRuleTest
 *
 * @package Spiral\Tests\Security\Rules
 */
class AllowRuleTest extends TestCase
{
    public const OPERATION = 'test';
    public const CONTEXT = [];

    public function testAllow(): void
    {
        /** @var RuleInterface $rule */
        $rule = new AllowRule();
        /** @var ActorInterface $actor */
        $actor = $this->createMock(ActorInterface::class);

        self::assertTrue($rule->allows($actor, static::OPERATION, static::CONTEXT));
    }
}

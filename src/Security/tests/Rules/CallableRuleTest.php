<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Rules;

use PHPUnit\Framework\TestCase;
use Spiral\Security\ActorInterface;
use Spiral\Security\RuleInterface;
use Spiral\Security\Rule\CallableRule;

final class CallableRuleTest extends TestCase
{
    public const OPERATION = 'test';
    public const CONTEXT = [];

    public function testAllow(): void
    {
        /** @var ActorInterface $actor */
        $actor = $this->createMock(ActorInterface::class);
        $context = [];

        /** @var \PHPUnit\Framework\MockObject\MockObject|callable $callable */
        $callable = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();

        $callable->method('__invoke')
            ->with($actor, self::OPERATION, $context)
            ->willReturn(true, false);

        /** @var RuleInterface $rule */
        $rule = new CallableRule($callable);

        self::assertTrue($rule->allows($actor, self::OPERATION, $context));
        self::assertFalse($rule->allows($actor, self::OPERATION, $context));
    }
}

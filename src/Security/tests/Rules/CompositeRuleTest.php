<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Rules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls;
use PHPUnit\Framework\TestCase;
use Spiral\Security\ActorInterface;
use Spiral\Security\RuleInterface;
use Spiral\Security\RulesInterface;
use Spiral\Tests\Security\Rules\Fixtures\AllCompositeRule;
use Spiral\Tests\Security\Rules\Fixtures\OneCompositeRule;

class CompositeRuleTest extends TestCase
{
    public const OPERATION = 'test';
    public const CONTEXT = [];

    private MockObject&ActorInterface $actor;

    public static function allowsProvider(): \Traversable
    {
        $allowRule = self::allowRule();
        $forbidRule = self::forbidRule();

        yield [true, AllCompositeRule::class, [$allowRule, $allowRule, $allowRule]];
        yield [false, AllCompositeRule::class, [$allowRule, $allowRule, $forbidRule]];
        yield [true, OneCompositeRule::class, [$allowRule, $forbidRule, $forbidRule]];
        yield [true, OneCompositeRule::class, [$allowRule, $allowRule, $allowRule]];
        yield [false, OneCompositeRule::class, [$forbidRule, $forbidRule, $forbidRule]];
    }

    #[DataProvider('allowsProvider')]
    public function testAllow(bool $expected, string $compositeRuleClass, array $rules): void
    {
        $repository = $this->createRepository($rules);

        /** @var RuleInterface $rule */
        $rule = new $compositeRuleClass($repository);
        self::assertEquals($expected, $rule->allows($this->actor, static::OPERATION, static::CONTEXT));
    }

    protected function setUp(): void
    {
        $this->actor = $this->createMock(ActorInterface::class);
    }

    private static function allowRule(): RuleInterface
    {
        $rule = \Mockery::mock(RuleInterface::class);
        $rule->shouldReceive('allows')->andReturnTrue();

        return $rule;
    }

    private static function forbidRule(): RuleInterface
    {
        $rule = \Mockery::mock(RuleInterface::class);
        $rule->shouldReceive('allows')->andReturnFalse();

        return $rule;
    }

    private function createRepository(array $rules): RulesInterface
    {
        /** @var MockObject|RulesInterface $repository */
        $repository = $this->createMock(RulesInterface::class);

        $repository->method('get')
            ->will(new ConsecutiveCalls($rules));

        return $repository;
    }
}

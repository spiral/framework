<?php

declare(strict_types=1);

namespace Spiral\Tests\Security;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Security\Exception\RuleException;
use Spiral\Security\RuleInterface;
use Spiral\Security\RuleManager;
use Spiral\Security\Rule\CallableRule;

/**
 * Class RuleManagerTest
 *
 * @package Spiral\Tests\Security
 */
class RuleManagerTest extends TestCase
{
    public const RULE_NAME = 'test';

    /** @var ContainerInterface */
    private $container;

    /** @var RuleInterface */
    private $rule;

    public function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);
        $this->rule = m::mock(RuleInterface::class);
    }

    public function testFlow(): void
    {
        $ruleClass = $this->rule::class;

        $this->container->shouldReceive('get')
            ->once()
            ->with($ruleClass)
            ->andReturn($this->rule);

        $manager = new RuleManager($this->container);

        $this->assertEquals($manager, $manager->set(self::RULE_NAME, $ruleClass));
        $this->assertTrue($manager->has(self::RULE_NAME));
        $this->assertEquals($this->rule, $manager->get(self::RULE_NAME));
        $this->assertEquals($manager, $manager->remove(self::RULE_NAME));

        // other rule types
        $manager->set('RuleInterface', $this->rule);
        $this->assertEquals($this->rule, $manager->get('RuleInterface'));
        $manager->set('Closure', static fn(): bool => true);
        $this->assertInstanceOf(\Spiral\Security\Rule\CallableRule::class, $manager->get('Closure'));
        $manager->set('Array', $this->testFlow(...));
        $this->assertInstanceOf(\Spiral\Security\Rule\CallableRule::class, $manager->get('Array'));
    }

    public function testHasWithNotRegisteredClass(): void
    {
        $ruleClass = $this->rule::class;
        $manager = new RuleManager($this->container);

        $this->assertTrue($manager->has($ruleClass));
    }

    public function testSetRuleException(): void
    {
        $manager = new RuleManager($this->container);

        $this->expectException(RuleException::class);
        $manager->set(self::RULE_NAME);
    }

    public function testRemoveException(): void
    {
        $this->container->shouldReceive('has')
            ->with(self::RULE_NAME)
            ->andReturnFalse();

        $manager = new RuleManager($this->container);

        $this->expectException(RuleException::class);
        $manager->remove(self::RULE_NAME);
    }

    public function testGetWithUndefinedRule(): void
    {
        $this->container->shouldReceive('has')
            ->with(self::RULE_NAME)
            ->andReturnFalse();

        $manager = new RuleManager($this->container);

        $this->expectException(RuleException::class);
        $manager->get(static::RULE_NAME);
    }

    public function testGetWithSomethingOtherThanRule(): void
    {
        $ruleClass = \stdClass::class;
        $this->container->shouldReceive('has')
            ->with(self::RULE_NAME)
            ->andReturnTrue();

        $this->container->shouldReceive('get')
            ->with($ruleClass)
            ->andReturn(new $ruleClass);

        $manager = new RuleManager($this->container);

        $this->expectException(RuleException::class);
        $manager->get($ruleClass);
    }
}

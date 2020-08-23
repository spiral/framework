<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Security;

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

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RuleInterface
     */
    private $rule;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->rule = $this->createMock(RuleInterface::class);
    }

    public function testFlow(): void
    {
        $ruleClass = get_class($this->rule);

        $this->container->expects($this->once())->method('get')
            ->with($ruleClass)->willReturn($this->rule);

        $manager = new RuleManager($this->container);

        $this->assertEquals($manager, $manager->set(self::RULE_NAME, $ruleClass));
        $this->assertTrue($manager->has(self::RULE_NAME));
        $this->assertEquals($this->rule, $manager->get(self::RULE_NAME));
        $this->assertEquals($manager, $manager->remove(self::RULE_NAME));

        // other rule types
        $manager->set('RuleInterface', $this->rule);
        $this->assertEquals($this->rule, $manager->get('RuleInterface'));
        $manager->set('Closure', function () {
            return true;
        });
        $this->assertTrue($manager->get('Closure') instanceof CallableRule);
        $manager->set('Array', [$this, 'testFlow']);
        $this->assertTrue($manager->get('Array') instanceof CallableRule);
    }

    public function testHasWithNotRegisteredClass(): void
    {
        $ruleClass = get_class($this->rule);
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
        $this->container->method('has')->with(self::RULE_NAME)->willReturn(false);

        $manager = new RuleManager($this->container);

        $this->expectException(RuleException::class);
        $manager->remove(self::RULE_NAME);
    }

    public function testGetWithUndefinedRule(): void
    {
        $this->container->method('has')->with(self::RULE_NAME)->willReturn(false);

        $manager = new RuleManager($this->container);

        $this->expectException(RuleException::class);
        $manager->get(static::RULE_NAME);
    }

    public function testGetWithSomethingOtherThanRule(): void
    {
        $ruleClass = \stdClass::class;
        $this->container->method('has')->with(self::RULE_NAME)->willReturn(true);

        $manager = new RuleManager($this->container);

        $this->expectException(RuleException::class);
        $manager->get($ruleClass);
    }
}

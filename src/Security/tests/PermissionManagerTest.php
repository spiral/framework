<?php

declare(strict_types=1);

namespace Spiral\Tests\Security;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spiral\Security\Exception\PermissionException;
use Spiral\Security\Exception\RoleException;
use Spiral\Security\PermissionManager;
use Spiral\Security\Rule\AllowRule;
use Spiral\Security\Rule\ForbidRule;
use Spiral\Security\RuleInterface;
use Spiral\Security\RulesInterface;

/**
 * Class PermissionManagerTest
 *
 * @package Spiral\Tests\Security
 */
class PermissionManagerTest extends TestCase
{
    public const ROLE = 'test';
    public const PERMISSION = 'permission';

    private MockObject&RulesInterface $rules;

    public function setUp(): void
    {
        $this->rules = $this->createMock(RulesInterface::class);
    }

    public function testRoles(): void
    {
        $manager = new PermissionManager($this->rules);

        self::assertFalse($manager->hasRole(static::ROLE));
        self::assertEquals($manager, $manager->addRole(static::ROLE));
        self::assertTrue($manager->hasRole(static::ROLE));
        self::assertEquals($manager, $manager->removeRole(static::ROLE));
        self::assertFalse($manager->hasRole(static::ROLE));

        $manager->addRole('one');
        $manager->addRole('two');
        self::assertSame(['one', 'two'], $manager->getRoles());
    }

    public function testAddRoleException(): void
    {
        $manager = new PermissionManager($this->rules);

        $this->expectException(RoleException::class);
        $manager->addRole(static::ROLE);
        $manager->addRole(static::ROLE);
    }

    public function testRemoveRoleException(): void
    {
        $manager = new PermissionManager($this->rules);

        $this->expectException(RoleException::class);
        $manager->removeRole(static::ROLE);
    }

    public function testAssociation(): void
    {
        $allowRule = new AllowRule();
        $forbidRule = new ForbidRule();

        $series = [
            [[AllowRule::class], $allowRule],
            [[AllowRule::class], $allowRule],
            [[ForbidRule::class], $forbidRule],
        ];

        $this->rules->method('has')->willReturn(true);
        $this->rules->method('get')
            ->willReturnCallback(function (...$args) use (&$series) {
                [$expectedArgs, $return] = \array_shift($series);
                self::assertSame($expectedArgs, $args);

                return $return;
            });

        $manager = new PermissionManager($this->rules);
        $manager->addRole(static::ROLE);

        // test simple permission
        self::assertEquals($manager, $manager->associate(static::ROLE, static::PERMISSION, AllowRule::class));
        self::assertEquals($allowRule, $manager->getRule(static::ROLE, static::PERMISSION));

        // test pattern permission
        self::assertEquals($manager, $manager->associate(static::ROLE, static::PERMISSION . '.*', AllowRule::class));
        self::assertEquals($allowRule, $manager->getRule(static::ROLE, static::PERMISSION . '.' . static::PERMISSION));

        self::assertEquals($manager, $manager->deassociate(static::ROLE, static::PERMISSION));
        self::assertEquals($forbidRule, $manager->getRule(static::ROLE, static::PERMISSION));
    }

    public function testGetRuleRoleException(): void
    {
        $manager = new PermissionManager($this->rules);

        $this->expectException(RoleException::class);
        $manager->getRule(static::ROLE, static::PERMISSION);
    }

    public function testRulesForRoleException(): void
    {
        $this->rules->method('has')->willReturn(true);
        $manager = new PermissionManager($this->rules);

        $this->expectException(RoleException::class);
        $manager->getPermissions('admin');
    }

    public function testRulesForRole(): void
    {
        $this->rules->method('has')->willReturn(true);

        $manager = new PermissionManager($this->rules);

        $manager->addRole('admin');
        $manager->associate('admin', 'post.edit', AllowRule::class);

        self::assertSame([
            'post.edit' => AllowRule::class
        ], $manager->getPermissions('admin'));
    }

    public function testGetFallbackRule(): void
    {
        $manager = new PermissionManager($this->rules);
        $manager->addRole(static::ROLE);

        $this->rules->method('get')
            ->with(ForbidRule::class)
            ->willReturn(new ForbidRule());

        self::assertInstanceOf(ForbidRule::class, $manager->getRule(static::ROLE, static::PERMISSION));
    }

    public function testAssociateRoleException(): void
    {
        $manager = new PermissionManager($this->rules);

        $this->expectException(RoleException::class);
        $manager->associate(static::ROLE, static::PERMISSION);
    }

    public function testAssociatePermissionException(): void
    {
        $this->expectException(PermissionException::class);

        $manager = new PermissionManager($this->rules);
        $manager->addRole(static::ROLE);
        $manager->associate(static::ROLE, static::PERMISSION);
    }
}

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
use Spiral\Security\RulesInterface;

/**
 * Class PermissionManagerTest
 *
 * @package Spiral\Tests\Security
 */
final class PermissionManagerTest extends TestCase
{
    public const ROLE = 'test';
    public const PERMISSION = 'permission';

    private MockObject&RulesInterface $rules;

    public function testRoles(): void
    {
        $manager = new PermissionManager($this->rules);

        self::assertFalse($manager->hasRole(self::ROLE));
        self::assertEquals($manager, $manager->addRole(self::ROLE));
        self::assertTrue($manager->hasRole(self::ROLE));
        self::assertEquals($manager, $manager->removeRole(self::ROLE));
        self::assertFalse($manager->hasRole(self::ROLE));

        $manager->addRole('one');
        $manager->addRole('two');
        self::assertSame(['one', 'two'], $manager->getRoles());
    }

    public function testAddRoleException(): void
    {
        $manager = new PermissionManager($this->rules);

        $this->expectException(RoleException::class);
        $manager->addRole(self::ROLE);
        $manager->addRole(self::ROLE);
    }

    public function testRemoveRoleException(): void
    {
        $manager = new PermissionManager($this->rules);

        $this->expectException(RoleException::class);
        $manager->removeRole(self::ROLE);
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
            ->willReturnCallback(static function (...$args) use (&$series) {
                [$expectedArgs, $return] = \array_shift($series);
                self::assertSame($expectedArgs, $args);

                return $return;
            });

        $manager = new PermissionManager($this->rules);
        $manager->addRole(self::ROLE);

        // test simple permission
        self::assertEquals($manager, $manager->associate(self::ROLE, self::PERMISSION, AllowRule::class));
        self::assertEquals($allowRule, $manager->getRule(self::ROLE, self::PERMISSION));

        // test pattern permission
        self::assertEquals($manager, $manager->associate(self::ROLE, self::PERMISSION . '.*', AllowRule::class));
        self::assertEquals($allowRule, $manager->getRule(self::ROLE, self::PERMISSION . '.' . self::PERMISSION));

        self::assertEquals($manager, $manager->deassociate(self::ROLE, self::PERMISSION));
        self::assertEquals($forbidRule, $manager->getRule(self::ROLE, self::PERMISSION));
    }

    public function testGetRuleRoleException(): void
    {
        $manager = new PermissionManager($this->rules);

        $this->expectException(RoleException::class);
        $manager->getRule(self::ROLE, self::PERMISSION);
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
            'post.edit' => AllowRule::class,
        ], $manager->getPermissions('admin'));
    }

    public function testGetFallbackRule(): void
    {
        $manager = new PermissionManager($this->rules);
        $manager->addRole(self::ROLE);

        $this->rules->method('get')
            ->with(ForbidRule::class)
            ->willReturn(new ForbidRule());

        self::assertInstanceOf(ForbidRule::class, $manager->getRule(self::ROLE, self::PERMISSION));
    }

    public function testAssociateRoleException(): void
    {
        $manager = new PermissionManager($this->rules);

        $this->expectException(RoleException::class);
        $manager->associate(self::ROLE, self::PERMISSION);
    }

    public function testAssociatePermissionException(): void
    {
        $this->expectException(PermissionException::class);

        $manager = new PermissionManager($this->rules);
        $manager->addRole(self::ROLE);
        $manager->associate(self::ROLE, self::PERMISSION);
    }

    protected function setUp(): void
    {
        $this->rules = $this->createMock(RulesInterface::class);
    }
}

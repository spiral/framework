<?php

declare(strict_types=1);

namespace Spiral\Tests\Security;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spiral\Security\ActorInterface;
use Spiral\Security\Exception\GuardException;
use Spiral\Security\Guard;
use Spiral\Security\PermissionsInterface;
use Spiral\Security\RuleInterface;

final class GuardTest extends TestCase
{
    public const OPERATION = 'test';
    public const CONTEXT = [];

    private MockObject&PermissionsInterface $permission;
    private \PHPUnit\Framework\MockObject\Stub&ActorInterface $actor;
    private array $roles = ['user', 'admin'];

    public function testAllows(): void
    {
        $this->permission->method('hasRole')
            ->willReturnCallback(static function (...$args): bool {
                static $series = [
                    [['user'], false],
                    [['admin'], true],
                ];

                [$expectedArgs, $return] = \array_shift($series);
                self::assertSame($expectedArgs, $args);

                return $return;
            });

        $rule = $this->createMock(RuleInterface::class);
        $rule->expects($this->once())
            ->method('allows')
            ->with($this->actor, self::OPERATION, [])->willReturn(true);

        $this->permission->method('getRule')
            ->willReturn($rule);

        $guard = new Guard($this->permission, $this->actor, $this->roles);
        self::assertTrue($guard->allows(self::OPERATION, self::CONTEXT));
    }

    public function testAllowsPermissionsHasNoRole(): void
    {
        $this->permission->method('hasRole')->with($this->anything())->willReturn(false);

        $guard = new Guard($this->permission, $this->actor, $this->roles);
        self::assertFalse($guard->allows(self::OPERATION, self::CONTEXT));
    }

    public function testAllowsNoActor(): void
    {
        $guard = new Guard($this->permission, null, $this->roles);

        $this->expectException(GuardException::class);
        $guard->allows(self::OPERATION, self::CONTEXT);
    }

    public function testWithActor(): void
    {
        $guard = new Guard($this->permission);
        $guardWithActor = $guard->withActor($this->actor);

        self::assertEquals($this->actor, $guardWithActor->getActor());
        self::assertNotEquals($guard, $guardWithActor);
    }

    public function testWithRoles(): void
    {
        $guard = new Guard($this->permission, $this->actor);
        $guardWithRoles = $guard->withRoles($this->roles);

        self::assertEquals($this->roles, $guardWithRoles->getRoles());
        self::assertNotEquals($guard, $guardWithRoles);
    }

    protected function setUp(): void
    {
        $this->permission = $this->createMock(PermissionsInterface::class);
        $this->actor = $this->createStub(ActorInterface::class);
    }
}

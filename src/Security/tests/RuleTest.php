<?php

declare(strict_types=1);

namespace Spiral\Tests\Security;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spiral\Core\ResolverInterface;
use Spiral\Security\ActorInterface;
use Spiral\Security\Exception\RuleException;
use Spiral\Security\Rule;

/**
 * Class RuleTest
 *
 * @package Spiral\Tests\Security
 */
class RuleTest extends TestCase
{
    public const OPERATION = 'test';
    public const CONTEXT = [];

    private MockObject&ActorInterface $actor;
    private MockObject&ResolverInterface $resolver;
    private MockObject&Rule $rule;

    public static function allowsProvider(): \Traversable
    {
        yield ['test.create', [], false];
        yield ['test.create', [], true];
        yield ['test.create', ['a' => 'b'], false];
        yield ['test.create', ['a' => 'b'], true];
    }

    #[DataProvider('allowsProvider')]
    public function testAllows(string $permission, array $context, bool $allowed): void
    {
        $parameters = [
            'actor'      => $this->actor,
            'user'       => $this->actor,
            'permission' => $permission,
            'context'    => $context,
        ] + $context;

        $method = new \ReflectionMethod($this->rule, 'check');
        $this->resolver
            ->expects($this->once())
            ->method('resolveArguments')
            ->with($method, $parameters)
            ->willReturn([$parameters]);

        $this->rule
            ->expects($this->once())
            ->method('check')
            ->with($parameters)
            ->willReturn($allowed);

        self::assertSame($allowed, $this->rule->allows($this->actor, $permission, $context));
    }

    public function testAllowsException(): void
    {
        $this->expectException(RuleException::class);
        $this->rule->allows($this->actor, static::OPERATION, static::CONTEXT);
    }

    protected function setUp(): void
    {
        $this->actor = $this->createMock(ActorInterface::class);
        $this->resolver = $this->createMock(ResolverInterface::class);
        $this->rule = $this->getMockBuilder(Rule::class)
            ->setConstructorArgs([$this->resolver])
            ->addMethods(['check'])->getMock();
    }
}

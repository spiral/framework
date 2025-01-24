<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Config;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use Spiral\Core\Exception\Resolver\MissingRequiredArgumentException;
use Spiral\Core\Exception\Resolver\PositionalArgumentException;
use Spiral\Core\Exception\Resolver\UnknownParameterException;
use Spiral\Core\Exception\Resolver\ValidationException;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\Internal\Resolver;
use Spiral\Core\Internal\Scope;
use Spiral\Core\Internal\State;
use Spiral\Core\ResolverInterface;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineZIL130;
use Spiral\Tests\Core\Stub\MadeInUssrInterface;

final class TypeValidationTest extends TestCase
{
    protected Registry $constructor;
    protected Config $config;

    // Successful cases

    public function testEmptySignatureWithoutArgs(): void
    {
        $this->validateClosureArguments(
            fn() => null,
            [],
        );
    }

    public function testEmptySignatureWithArgs(): void
    {
        $this->validateClosureArguments(
            fn() => null,
            ['foo', 'bar'],
        );
    }

    public function testScalarParamsWithArgs(): void
    {
        $this->validateClosureArguments(
            fn(int $a, string $b, float $c, bool $e) => null,
            [42, 'foo', .1, false],
        );
    }

    public function testScalarNullableParamsWithArgs(): void
    {
        $this->validateClosureArguments(
            fn(?int $a, ?string $b, ?float $c, ?bool $e) => null,
            [42, 'foo', .1, false],
        );
    }

    public function testMixedAndVariadicParamsWithArgs(): void
    {
        $this->validateClosureArguments(
            fn(mixed $a, $b, ...$e) => null,
            [42, 'foo', .1, false],
        );
    }

    public function testFloatWithNan(): void
    {
        $this->validateClosureArguments(
            fn(float $b) => null,
            [NAN],
        );
    }

    public function testIterable(): void
    {
        $this->validateClosureArguments(
            fn(iterable $a, iterable $b) => null,
            [[1, 2, NAN], new \EmptyIterator()],
        );
    }

    public function testArray(): void
    {
        $this->validateClosureArguments(
            fn(array $b) => null,
            [[1, 2, NAN]],
        );
    }

    public function testObject(): void
    {
        $this->validateClosureArguments(
            fn(object $a, object $b) => null,
            [new \stdClass(), new \DateTimeImmutable()],
        );
    }

    public function testCallable(): void
    {
        $this->validateClosureArguments(
            fn(callable $a, callable $b) => null,
            [fn() => true, [$this, 'testCallable']],
        );
    }

    public function testInterfaceAndClass(): void
    {
        $this->validateClosureArguments(
            fn(\DateTimeInterface $a, \DateTimeImmutable $b) => null,
            [new \DateTimeImmutable(), new \DateTimeImmutable()],
        );
    }

    public function testUnionType(): void
    {
        $this->validateClosureArguments(
            fn(array|\Traversable $a, array|\Traversable $b) => null,
            [[1, 2, NAN], new \EmptyIterator()],
        );
    }

    public function testNullableUnionType(): void
    {
        $this->validateClosureArguments(
            fn(null|array|\Traversable $a, null|array|\Traversable $b) => null,
            [null, new \EmptyIterator()],
        );
    }

    public function testTypeIntersection(): void
    {
        $this->validateClosureArguments(
            fn(EngineInterface&MadeInUssrInterface $a) => null,
            [new EngineZIL130()],
        );
    }

    public function testMissingOptionalArguments(): void
    {
        $this->validateClosureArguments(
            $fn = fn(int $b, int $a = 0, $c = null) => \func_get_args(),
            $args = [1],
        );
        $this->assertSame($args, $fn(...$args));
    }

    public function testVariadicParamWithoutArguments(): void
    {
        $this->validateClosureArguments(
            $fn = fn(EngineInterface ...$engines) => $engines,
            $args = [],
        );
        $this->assertSame($args, $fn(...$args));
    }

    // Exceptions

    public function testWrongIntStrict(): void
    {
        $this->validateClosureArguments(
            fn(int $a) => null,
            ['42'],
            'a',
        );
    }

    public function testWrongStringStrict(): void
    {
        $this->validateClosureArguments(
            fn(string $a) => null,
            [42],
            'a',
        );
    }

    public function testWrongArrayStrict(): void
    {
        $this->validateClosureArguments(
            fn(array $a) => null,
            [null],
            'a',
        );
    }

    public function testWrongUnionType(): void
    {
        $this->validateClosureArguments(
            fn(array|\Traversable $a) => null,
            ['foo'],
            'a',
        );
    }

    public function testWrongTypeIntersection(): void
    {
        $this->validateClosureArguments(
            fn(EngineInterface&MadeInUssrInterface $a) => null,
            [new EngineMarkTwo()],
            'a',
        );
    }

    // positioning and named arguments

    public function testOneNamedArgument(): void
    {
        $this->validateClosureArguments(
            fn(EngineInterface&MadeInUssrInterface $a) => null,
            ['a' => new EngineZIL130()],
        );
    }

    public function testOnePositionalOneNamedArguments(): void
    {
        $this->validateClosureArguments(
            fn(int $a, string $b) => null,
            [42, 'b' => 'bar'],
        );
    }

    public function testOnePositionalOneNamedArgumentsSkipOptional(): void
    {
        $this->validateClosureArguments(
            $fn = fn(int $a, ?\stdClass $b = null, string $c = 'bar') => [$a, $b, $c],
            $args = [42, 'c' => 'bar'],
        );
        $this->assertSame([42, null, 'bar'], $fn(...$args));
    }

    public function testShuffledPositionalArgs(): void
    {
        $this->validateClosureArguments(
            $fn = fn(int $a, int $b, int $c) => [$a, $b, $c],
            $args = [1 => 1, 2 => 2, 0 => 0],
        );
        $this->assertSame([1, 2, 0], $fn(...$args));
    }

    public function testVariadicParameterWithPositionalArgs(): void
    {
        $this->validateClosureArguments(
            $fn = fn(int ...$c) => $c,
            $args = [1 => 1, 2 => 2, 0 => 0],
        );
        $this->assertSame([1, 2, 0], $fn(...$args));
    }

    public function testVariadicParameterWithPositionalAnNamedArgs(): void
    {
        $this->validateClosureArguments(
            $fn = fn(int ...$c) => $c,
            $args = [1 => 1, 2 => 2, 0 => 0, 'foo' => 42, 'bar' => 0],
        );
        $this->assertSame([1, 2, 0, 'foo' => 42, 'bar' => 0], $fn(...$args));
    }

    public function testVariadicParameterWithWrongPositionalArgs(): void
    {
        $this->validateClosureArguments(
            fn(int $i, int ...$c) => $c,
            [0, 1, 2, 'foo'],
            'c',
        );
    }

    public function testUnusedNamedArgs(): void
    {
        $this->validateClosureArguments(
            fn(int $b, int $a) => \func_get_args(),
            [1, 'a' => 0, 'c' => 2],
            invalidParameter: 'c',
            exceptionClass: UnknownParameterException::class,
        );
    }

    public function testMissingRequiredArgument(): void
    {
        $this->validateClosureArguments(
            fn(int $b, int $a) => \func_get_args(),
            [1],
            invalidParameter: 'a',
            exceptionClass: MissingRequiredArgumentException::class,
        );
    }

    public function testPositionalArgsAfterNamedVariadic(): void
    {
        $this->validateClosureArguments(
            fn(string $a, ...$b) => \func_get_args(),
            ['a' => 'foo', 's' => 'ff', 'bar'],
            invalidParameter: '#0',
            exceptionClass: PositionalArgumentException::class,
        );
    }

    public function testPositionalArgsAfterNamed(): void
    {
        $this->validateClosureArguments(
            fn(string $a, $b = null, $c = null) => \func_get_args(),
            ['a' => 'foo', 'bar'],
            invalidParameter: '#0',
            exceptionClass: PositionalArgumentException::class,
        );
    }

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->constructor = new Registry($this->config, [
            'state' => new State(),
            'scope' => new Scope(),
        ]);
        parent::setUp();
    }

    private function validateClosureArguments(
        \Closure $closure,
        array $arguments = [],
        ?string $invalidParameter = null,
        string $exceptionClass = InvalidArgumentException::class,
    ): void {
        try {
            $this->createResolver()->validateArguments(new \ReflectionFunction($closure), $arguments);
        } catch (ValidationException $e) {
            $this->assertInstanceOf($exceptionClass, $e, 'Expected other exception.');
            if ($invalidParameter === null) {
                throw $e;
            }
            if ($e->getParameter() !== $invalidParameter) {
                $this->fail(
                    \sprintf(
                        'The other argument has been failed: `%s` instead of `%s`.',
                        $e->getParameter(),
                        $invalidParameter,
                    ),
                );
            }
            $this->assertTrue(true, 'Invalid value has been failed.');
            return;
        }
        if ($invalidParameter === null) {
            $this->assertTrue(true, 'Valid argument value has been failed.');
            return;
        }
        $this->fail('Invalid arguments have been passed.');
    }

    private function createResolver(): ResolverInterface
    {
        return new Resolver($this->constructor);
    }
}

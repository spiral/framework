<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use EmptyIterator;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use Spiral\Core\Config;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use Spiral\Core\Internal\Constructor;
use Spiral\Core\Internal\Resolver;
use Spiral\Core\Internal\State;
use Spiral\Core\ResolverInterface;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineZIL130;
use Spiral\Tests\Core\Stub\MadeInUssrInterface;
use stdClass;
use Traversable;

final class TypeValidationTest extends TestCase
{
    protected Constructor $constructor;
    protected Config $config;

    // Successful cases

    public function testEmptySignatureWithoutArgs(): void
    {
        $this->validateClosureArguments(
            fn () => null,
            []
        );
    }

    public function testEmptySignatureWithArgs(): void
    {
        $this->validateClosureArguments(
            fn () => null,
            ['foo', 'bar']
        );
    }

    public function testScalarParamsWithArgs(): void
    {
        $this->validateClosureArguments(
            fn (int $a, string $b, float $c, bool $e) => null,
            [42, 'foo', .1, false]
        );
    }

    public function testScalarNullableParamsWithArgs(): void
    {
        $this->validateClosureArguments(
            fn (?int $a, ?string $b, ?float $c, ?bool $e) => null,
            [42, 'foo', .1, false]
        );
    }

    public function testMixedAndVariadicParamsWithArgs(): void
    {
        $this->validateClosureArguments(
            fn (mixed $a, $b, ...$e) => null,
            [42, 'foo', .1, false]
        );
    }

    public function testFloatWithNan(): void
    {
        $this->validateClosureArguments(
            fn (float $b) => null,
            [NAN]
        );
    }

    public function testIterable(): void
    {
        $this->validateClosureArguments(
            fn (iterable $a, iterable $b) => null,
            [[1, 2, NAN], new EmptyIterator()]
        );
    }

    public function testArray(): void
    {
        $this->validateClosureArguments(
            fn (array $b) => null,
            [[1, 2, NAN]]
        );
    }

    public function testObject(): void
    {
        $this->validateClosureArguments(
            fn (object $a, object $b) => null,
            [new stdClass(), new DateTimeImmutable()]
        );
    }

    public function testInterfaceAndClass(): void
    {
        $this->validateClosureArguments(
            fn (DateTimeInterface $a, DateTimeImmutable $b) => null,
            [new DateTimeImmutable(), new DateTimeImmutable()]
        );
    }

    public function testUnionType(): void
    {
        $this->validateClosureArguments(
            fn (array|Traversable $a, array|Traversable $b) => null,
            [[1, 2, NAN], new EmptyIterator()]
        );
    }

    public function testNullableUnionType(): void
    {
        $this->validateClosureArguments(
            fn (null|array|Traversable $a, null|array|Traversable $b) => null,
            [null, new EmptyIterator()]
        );
    }

    public function testTypeIntersection(): void
    {
        $this->validateClosureArguments(
            fn (EngineInterface&MadeInUssrInterface $a) => null,
            [new EngineZIL130()]
        );
    }

    // Exceptions

    public function testWrongIntStrict(): void
    {
        $this->validateClosureArguments(
            fn (int $a) => null,
            ['42'],
            'a'
        );
    }

    public function testWrongStringStrict(): void
    {
        $this->validateClosureArguments(
            fn (string $a) => null,
            [42],
            'a'
        );
    }

    public function testWrongArrayStrict(): void
    {
        $this->validateClosureArguments(
            fn (array $a) => null,
            [null],
            'a'
        );
    }

    public function testWrongUnionType(): void
    {
        $this->validateClosureArguments(
            fn (array|Traversable $a) => null,
            ['foo'],
            'a'
        );
    }

    public function testWrongTypeIntersection(): void
    {
        $this->validateClosureArguments(
            fn (EngineInterface&MadeInUssrInterface $a) => null,
            [new EngineMarkTwo()],
            'a'
        );
    }

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->constructor = new Constructor($this->config, [
            'state' => new State(),
        ]);
        parent::setUp();
    }

    private function validateClosureArguments(
        Closure $closure,
        array $arguments = [],
        ?string $invalidParameter = null
    ): void {
        try {
            $this->createResolver()->validateArguments(new ReflectionFunction($closure), $arguments);
        } catch (InvalidArgumentException $e) {
            if ($invalidParameter === null) {
                throw $e;
            }
            if ($e->getParameter() !== $invalidParameter) {
                $this->fail(
                    \sprintf(
                        'The other argument has been failed: `%s` instead of `%s`.',
                        $e->getParameter(),
                        $invalidParameter
                    )
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

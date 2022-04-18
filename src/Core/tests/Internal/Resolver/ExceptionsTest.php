<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTimeImmutable;
use Spiral\Core\Exception\Container\ArgumentException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Tests\Core\Stub\ColorInterface;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineVAZ2101;
use Spiral\Tests\Core\Stub\EngineZIL130;
use Spiral\Tests\Core\Stub\MadeInUssrInterface;
use stdClass;

final class ExceptionsTest extends BaseTest
{
    public function testMissingRequiredTypedParameter(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $this->expectException(ArgumentException::class);

        $this->resolveClosure(
            static fn(EngineInterface $engine, string $two) => null
        );
    }

    public function testMissingRequiredNotTypedParameter(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $this->expectException(ArgumentException::class);

        $this->resolveClosure(
            static fn(EngineInterface $engine, $two) => null
        );
    }

    public function testNotFoundException(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $this->expectException(NotFoundException::class);

        $this->resolveClosure(
            static fn(EngineInterface $engine, ColorInterface $color) => null
        );
    }

    /**
     * Required `object` type should not be requested from the container
     */
    public function testRequiredObjectTypeWithoutInstance(): void
    {
        $this->expectException(ArgumentException::class);

        $this->resolveClosure(
            static fn(object $engine) => null
        );
    }

    /**
     * Argument will be checked before result returning
     */
    public function testWrongNamedParam(): void
    {
        $this->expectException(\Throwable::class);

        $this->resolveClosure(fn(EngineInterface $engine) => null, ['engine' => new DateTimeImmutable()]);
    }

    /**
     * Failed argument will not be pulled from container?
     */
    public function testWrongNamedParamWithValueInContainer(): void
    {
        $this->bindSingleton(EngineInterface::class, $engine = new EngineMarkTwo());

        $this->expectException(\Throwable::class); # todo: determinate an exception

        $this->resolveClosure(
            static fn(EngineInterface $engine) => null,
            ['engine' => new DateTimeImmutable()]
        );
    }

    /**
     * Variadic arguments should be passed in a one array
     */
    public function testVariadicParameterAndUnpackedArguments(): void
    {
        $this->expectException(\Throwable::class); # todo: determinate an exception

        $this->resolveClosure(
            fn(EngineInterface ...$engines) => $engines,
            [new EngineZIL130(), new EngineVAZ2101(), new EngineMarkTwo()]
        );
    }

    public function testVariadicTypeIntersectionParameterAndUnnamedArguments(): void
    {
        $this->expectException(ArgumentException::class);

        $this->resolveClosure(
            fn(EngineInterface&MadeInUssrInterface ...$engines) => $engines,
            [[new EngineZIL130(), new EngineVAZ2101(), new stdClass(), new EngineMarkTwo(), new stdClass()]]
        );
    }
}

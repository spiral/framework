<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\Exception\Resolver\ResolvingException;
use Spiral\Tests\Core\Stub\ColorInterface;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;

final class ExceptionsTest extends BaseTest
{
    public function testMissingRequiredTypedParameter(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $this->expectException(ResolvingException::class);

        $this->resolveClosure(
            static fn(EngineInterface $engine, string $two) => null
        );
    }

    public function testMissingRequiredNotTypedParameter(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $this->expectException(ResolvingException::class);

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
        $this->expectException(ResolvingException::class);

        $this->resolveClosure(
            static fn(object $engine) => null
        );
    }
}

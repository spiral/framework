<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTimeImmutable;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use Spiral\Tests\Core\Stub\EngineInterface;
use stdClass;

final class ResolvingValidationTest extends BaseTest
{
    public function testNullInsteadOfClass(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->resolveClosure(
            static fn(EngineInterface $engine) => null,
            [null],
        );
    }

    public function testWrongClass(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->resolveClosure(
            static fn(EngineInterface $engine) => null,
            [new stdClass()],
        );
    }

    public function testWrongClassValidationOff(): void
    {
        $result = $this->resolveClosure(
            static fn(EngineInterface $engine) => null,
            [new stdClass()],
            validate: false
        );

        $this->assertInstanceOf(stdClass::class, $result[0]);
    }

    public function testVariadicWrongClass(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->resolveClosure(
            static fn(EngineInterface ...$engine) => null,
            ['engine' => [new stdClass(), new DateTimeImmutable()]],
        );
    }

    public function testVariadicWrongClassValidationOff(): void
    {
        $result = $this->resolveClosure(
            static fn(EngineInterface ...$engine) => null,
            ['engine' => [new stdClass(), new DateTimeImmutable()]],
            validate: false
        );

        $this->assertInstanceOf(stdClass::class, $result[0]);
        $this->assertInstanceOf(DateTimeImmutable::class, $result[1]);
    }

    public function testWrongClassFromContainer(): void
    {
        $this->bindSingleton(stdClass::class, new DateTimeImmutable());

        $this->expectException(InvalidArgumentException::class);

        $this->resolveClosure(
            static fn(stdClass $engine) => null,
        );
    }

    public function testWrongClassFromContainerValidationOff(): void
    {
        $this->bindSingleton(stdClass::class, new DateTimeImmutable());

        $result = $this->resolveClosure(
            static fn(stdClass $engine) => null,
            validate: false
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $result[0]);
    }
}

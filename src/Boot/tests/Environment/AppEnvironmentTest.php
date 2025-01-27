<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Environment;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\Environment\AppEnvironment;
use Spiral\Boot\EnvironmentInterface;

final class AppEnvironmentTest extends TestCase
{
    public static function envVariablesDataProvider(): \Traversable
    {
        yield ['wrong', AppEnvironment::Local];
        yield ['prod', AppEnvironment::Production];
        yield ['production', AppEnvironment::Production];
        yield ['stage', AppEnvironment::Stage];
        yield ['local', AppEnvironment::Local];
        yield ['dev', AppEnvironment::Local];
        yield ['development', AppEnvironment::Local];
        yield ['testing', AppEnvironment::Testing];
        yield ['test', AppEnvironment::Testing];
    }

    public function testDetectWithoutEnvironmentVariable(): void
    {
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->with('APP_ENV')->andReturnNull();

        $enum = AppEnvironment::detect($env);

        self::assertSame(AppEnvironment::Local, $enum);
    }

    #[DataProvider('envVariablesDataProvider')]
    public function testDetectWithWrongEnvironmentVariable($name, AppEnvironment $expected): void
    {
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->with('APP_ENV')->andReturn($name);

        $enum = AppEnvironment::detect($env);

        self::assertSame($expected, $enum);
    }

    public function testClassMethods(): void
    {
        $prod = AppEnvironment::Production;
        self::assertTrue($prod->isProduction());
        self::assertFalse($prod->isLocal());
        self::assertFalse($prod->isStage());
        self::assertFalse($prod->isTesting());

        $prod = AppEnvironment::Local;
        self::assertFalse($prod->isProduction());
        self::assertTrue($prod->isLocal());
        self::assertFalse($prod->isStage());
        self::assertFalse($prod->isTesting());

        $prod = AppEnvironment::Stage;
        self::assertFalse($prod->isProduction());
        self::assertFalse($prod->isLocal());
        self::assertTrue($prod->isStage());
        self::assertFalse($prod->isTesting());

        $prod = AppEnvironment::Testing;
        self::assertFalse($prod->isProduction());
        self::assertFalse($prod->isLocal());
        self::assertFalse($prod->isStage());
        self::assertTrue($prod->isTesting());
    }
}

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
    public function testDetectWithoutEnvironmentVariable(): void
    {
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->with('APP_ENV')->andReturnNull();

        $enum = AppEnvironment::detect($env);

        $this->assertSame(AppEnvironment::Local, $enum);
    }

    #[DataProvider('envVariablesDataProvider')]
    public function testDetectWithWrongEnvironmentVariable($name, AppEnvironment $expected): void
    {
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->with('APP_ENV')->andReturn($name);

        $enum = AppEnvironment::detect($env);

        $this->assertSame($expected, $enum);
    }

    public static function envVariablesDataProvider(): \Traversable
    {
        yield ['wrong', AppEnvironment::Local];
        yield ['prod', AppEnvironment::Production];
        yield ['stage', AppEnvironment::Stage];
        yield ['local', AppEnvironment::Local];
        yield ['testing', AppEnvironment::Testing];
    }

    public function testClassMethods(): void
    {
        $prod = AppEnvironment::Production;
        $this->assertTrue($prod->isProduction());
        $this->assertFalse($prod->isLocal());
        $this->assertFalse($prod->isStage());
        $this->assertFalse($prod->isTesting());

        $prod = AppEnvironment::Local;
        $this->assertFalse($prod->isProduction());
        $this->assertTrue($prod->isLocal());
        $this->assertFalse($prod->isStage());
        $this->assertFalse($prod->isTesting());

        $prod = AppEnvironment::Stage;
        $this->assertFalse($prod->isProduction());
        $this->assertFalse($prod->isLocal());
        $this->assertTrue($prod->isStage());
        $this->assertFalse($prod->isTesting());

        $prod = AppEnvironment::Testing;
        $this->assertFalse($prod->isProduction());
        $this->assertFalse($prod->isLocal());
        $this->assertFalse($prod->isStage());
        $this->assertTrue($prod->isTesting());
    }
}

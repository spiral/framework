<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Environment;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\Environment\DebugMode;
use Spiral\Boot\EnvironmentInterface;

final class DebugModeTest extends TestCase
{
    public function testDetectWithoutEnvironmentVariable(): void
    {
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->with('DEBUG')->andReturnNull();

        $enum = DebugMode::detect($env);

        $this->assertSame(DebugMode::Disabled, $enum);
    }

    /** @dataProvider envVariablesDataProvider */
    public function testDetectWithWrongEnvironmentVariable($name, DebugMode $expected): void
    {
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')->with('DEBUG')->andReturn($name);

        $enum = DebugMode::detect($env);

        $this->assertSame($expected, $enum);

        if ($enum === DebugMode::Enabled) {
            $this->assertTrue($enum->isEnabled());
        } else {
            $this->assertFalse($enum->isEnabled());
        }
    }

    public function envVariablesDataProvider()
    {
        return [
            [true, DebugMode::Enabled],
            ['true', DebugMode::Enabled],
            ['1', DebugMode::Enabled],
            ['on', DebugMode::Enabled],
            ['false', DebugMode::Disabled],
            ['0', DebugMode::Disabled],
            ['off', DebugMode::Disabled],
            [false, DebugMode::Disabled],
        ];
    }
}

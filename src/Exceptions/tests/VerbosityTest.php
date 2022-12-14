<?php

namespace Spiral\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Exceptions\Verbosity;
use Mockery as m;

final class VerbosityTest extends TestCase
{
    /** @dataProvider envVariablesDataProvider */
    public function testDetectEnvironmentVariable($name, Verbosity $expected): void
    {
        $env = m::mock(EnvironmentInterface::class);

        $env->shouldReceive('get')
            ->once()
            ->with('VERBOSITY_LEVEL')
            ->andReturn($name);

        $enum = Verbosity::detect($env);

        $this->assertSame($expected, $enum);
    }

    public function envVariablesDataProvider(): array
    {
        return [
            ['basic', Verbosity::BASIC],
            [0, Verbosity::BASIC],
            ['Basic', Verbosity::BASIC],
            ['debug', Verbosity::DEBUG],
            [2, Verbosity::DEBUG],
            ['invalid', Verbosity::VERBOSE],
            ['', Verbosity::VERBOSE],
            [null, Verbosity::VERBOSE],
            [true, Verbosity::VERBOSE],
            [false, Verbosity::VERBOSE],
        ];
    }
}

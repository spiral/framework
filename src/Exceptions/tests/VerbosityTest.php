<?php

namespace Spiral\Tests\Exceptions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Exceptions\Verbosity;
use Mockery as m;

final class VerbosityTest extends TestCase
{
    #[DataProvider('envVariablesDataProvider')]
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

    public static function envVariablesDataProvider(): \Traversable
    {
        yield ['basic', Verbosity::BASIC];
        yield [0, Verbosity::BASIC];
        yield ['Basic', Verbosity::BASIC];
        yield ['debug', Verbosity::DEBUG];
        yield [2, Verbosity::DEBUG];
        yield ['invalid', Verbosity::VERBOSE];
        yield ['', Verbosity::VERBOSE];
        yield [null, Verbosity::VERBOSE];
        yield [true, Verbosity::VERBOSE];
        yield [false, Verbosity::VERBOSE];
    }
}

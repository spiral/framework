<?php

declare(strict_types=1);

namespace Spiral\Tests\DotEnv;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\Directories;
use Spiral\Boot\EnvironmentInterface;
use Spiral\DotEnv\Bootloader\DotenvBootloader;

final class LoadTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSetValue(): void
    {
        $dirs = new Directories(['root' => __DIR__.'/']);

        $env = m::mock(EnvironmentInterface::class);
        $env
            ->shouldReceive('get')
            ->once()
            ->withSomeOfArgs('DOTENV_PATH')
            ->andReturn($dirs->get('root') . '.env.custom');
        $env->shouldReceive('set')
            ->once()->with('KEY', 'custom_value');

        $b = new DotenvBootloader();
        $b->init($dirs, $env);
    }

    public function testNotFound(): void
    {
        $dirs = new Directories(['root' => __DIR__.'/']);

        $env = m::mock(EnvironmentInterface::class);
        $env
            ->shouldReceive('get')
            ->once()
            ->withSomeOfArgs('DOTENV_PATH')
            ->andReturn($dirs->get('root').'.env');
        $env
            ->shouldNotReceive('set')
            ->with('KEY', 'custom_value');

        $b = new DotenvBootloader();
        $b->init($dirs, $env);
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Tests\DotEnv;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Directories;
use Spiral\Boot\EnvironmentInterface;
use Spiral\DotEnv\Bootloader\DotenvBootloader;

final class LoadTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testUseKernelCallback(): void
    {
        $d = new Directories(['root' => __DIR__.'/']);

        $e = m::mock(EnvironmentInterface::class);
        $e->shouldReceive('get')->once()->withSomeOfArgs('DOTENV_PATH')->andReturn($d->get('root').'.env.custom');
        $e->shouldReceive('set')->once()->with('KEY', 'custom_value');

        $k = m::mock(AbstractKernel::class);
        $k->shouldReceive('running')->once()->andReturnUsing(fn(\Closure $callback) => $callback($e));

        $b = new DotenvBootloader();
        $b->init($k, $d);
    }

    public function testNotFound(): void
    {
        $d = new Directories(['root' => __DIR__.'/']);

        $e = m::mock(EnvironmentInterface::class);
        $e->shouldReceive('get')->once()->withSomeOfArgs('DOTENV_PATH')->andReturn($d->get('root').'.env');
        $e->shouldNotReceive('set')->with('KEY', 'custom_value');

        $k = m::mock(AbstractKernel::class);
        $k->shouldReceive('running')->once()->andReturnUsing(fn(\Closure $callback) => $callback($e));

        $b = new DotenvBootloader();
        $b->init($k, $d);
    }
}

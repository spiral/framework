<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
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
        $k = m::mock(AbstractKernel::class);
        $k->shouldReceive('running')->once()->andReturnUsing(fn(\Closure $callback) => $callback());

        $d = new Directories(['root' => __DIR__.'/']);

        $e = m::mock(EnvironmentInterface::class);
        $e->shouldReceive('get')->once()->withSomeOfArgs('DOTENV_PATH')->andReturn($d->get('root').'.env.custom');
        $e->shouldReceive('set')->once()->with('KEY', 'custom_value');

        $b = new DotenvBootloader();
        $b->init($k, $d, $e);
    }

    public function testNotFound(): void
    {
        $k = m::mock(AbstractKernel::class);
        $k->shouldReceive('running')->once();

        $d = new Directories(['root' => __DIR__.'/']);

        $e = m::mock(EnvironmentInterface::class);
        $e->shouldReceive('get')->once()->withSomeOfArgs('DOTENV_PATH')->andReturn($d->get('root').'.env');
        $e->shouldNotReceive('set')->with('KEY', 'custom_value');

        $b = new DotenvBootloader();
        $b->init($k, $d, $e);
    }

    public function testFoundCustom(): void
    {
        $k = m::mock(AbstractKernel::class);
        $k->shouldReceive('running')->once();

        $d = new Directories(['root' => __DIR__.'/']);

        $e = m::mock(EnvironmentInterface::class);
        $e->shouldReceive('get')->once()->withSomeOfArgs('DOTENV_PATH')->andReturn($d->get('root').'.env.custom');
        $e->shouldReceive('set')->once()->with('KEY', 'custom_value');

        $b = new DotenvBootloader();
        $b->init($k, $d, $e);
    }
}

<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Mockery as m;
use Spiral\Core\BootloadManager;
use Spiral\Tests\BaseTest;
use Spiral\Tests\Core\Fixtures\SampleBoot;
use Spiral\Tests\Core\Fixtures\SampleClass;

class BootloadersTest extends BaseTest
{
    public function testSchemaLoading()
    {
        $bootloader = new BootloadManager($this->container, $this->memory);

        $this->assertEmpty($this->memory->loadData('sample-load'));
        $bootloader->bootload([SampleClass::class], 'sample-load');
        $this->assertNotEmpty($this->memory->loadData('sample-load'));

        $memory = m::mock($this->memory);
        $memory->shouldReceive('loadData')->with('sample-load')->andReturn([
            'snapshot'    =>
                [
                    0 => 'Spiral\\Tests\\Core\\Fixtures\\SampleClass',
                    1 => 'Spiral\\Tests\\Core\\Fixtures\\SampleBoot',
                ],
            'bootloaders' =>
                [
                    'Spiral\\Tests\\Core\\Fixtures\\SampleClass' =>
                        [
                            'init' => true,
                            'boot' => false,
                        ],
                    'Spiral\\Tests\\Core\\Fixtures\\SampleBoot'  =>
                        [
                            'init'       => false,
                            'boot'       => false,
                            'bindings'   =>
                                [
                                    'abc' => 'Spiral\\Tests\\Core\\Fixtures\\SampleBoot',
                                ],
                            'singletons' =>
                                [
                                ],
                        ],
                ],
        ]);

        $bootloader = new BootloadManager($this->container, $memory);
        $bootloader->bootload([SampleClass::class, SampleBoot::class], 'sample-load');

        $this->assertTrue($this->container->has('abc'));
    }
}
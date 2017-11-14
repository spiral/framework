<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Debug;

use Mockery as m;
use Psr\Log\LoggerInterface;
use Spiral\Debug\Configs\SnapshotConfig;
use Spiral\Debug\Snapshot;
use Spiral\Support\ExceptionHelper;
use Spiral\Tests\BaseTest;

class SnapshotTest extends BaseTest
{
    public function testGetException()
    {
        $snaphot = $this->makeSnapshot($e = new \Error("Error"));
        $this->assertSame($e, $snaphot->getException());
    }

    public function testGetMessage()
    {
        $snaphot = $this->makeSnapshot($e = new \Error("Error"));
        $this->assertSame(ExceptionHelper::createMessage($e), $snaphot->getMessage());
    }

    public function testDescribe()
    {
        $snaphot = $this->makeSnapshot($e = new \Error("Error"));
        $this->assertInternalType('array', $snaphot->describe());
    }

    public function testRender()
    {
        $snaphot = $this->makeSnapshot($e = new \Error("Error"));
        $this->assertContains($e->getMessage(), $snaphot->render());

        $this->assertSame($snaphot->render(), $snaphot->render());
    }

    public function testToString()
    {
        $snaphot = $this->makeSnapshot($e = new \Error("Error"));
        $this->assertContains($e->getMessage(), (string)$snaphot);
    }

    public function testLog()
    {
        $log = m::mock(LoggerInterface::class);
        $snaphot = $this->makeSnapshot($e = new \Error("Error"), ['logger' => $log]);

        $log->shouldReceive('error')->with($snaphot->getMessage());
        $snaphot->report();
    }

    public function testReportFast()
    {
        /**@var SnapshotConfig $config */
        $config = $this->container->get(SnapshotConfig::class);

        $mock = m::mock($config);
        $mock->shouldReceive('viewName')->andReturn('spiral:exceptions/light/fast');

        $snaphot = $this->makeSnapshot($e = new \Error("Error"), [
            'config' => $mock
        ]);

        $this->assertEmpty($this->files->getFiles($config->reportingDirectory()));
        $snaphot->report();
        $this->assertNotEmpty($this->files->getFiles($config->reportingDirectory()));
    }

    /**
     * This test will perform crazy amount of calculations due amount of dump commands and PHPUnit
     * stack.
     */
//     public function testReportSlow()
//     {
//         /**@var SnapshotConfig $config */
//         $config = $this->container->get(SnapshotConfig::class);

//         $mock = m::mock($config);
//         $mock->shouldReceive('viewName')->andReturn('spiral:exceptions/light/slow');

//         $snaphot = $this->makeSnapshot($e = new \Error("Error"), [
//             'config' => $mock
//         ]);

//         $this->assertEmpty($this->files->getFiles($config->reportingDirectory()));
//         $snaphot->report();
//         $this->assertNotEmpty($this->files->getFiles($config->reportingDirectory()));
//     }

    public function testReportFallback()
    {
        /**@var SnapshotConfig $config */
        $config = $this->container->get(SnapshotConfig::class);

        $mock = m::mock($config);
        $mock->shouldReceive('viewName')->andReturn('');

        $snaphot = $this->makeSnapshot($e = new \Error("Error"), [
            'config' => $mock
        ]);

        $this->assertEmpty($this->files->getFiles($config->reportingDirectory()));
        $snaphot->report();
        $this->assertNotEmpty($this->files->getFiles($config->reportingDirectory()));
    }

    public function testRotation()
    {
        /**@var SnapshotConfig $config */
        $config = $this->container->get(SnapshotConfig::class);

        $mock = m::mock($config);
        $mock->shouldReceive('viewName')->andReturn('');
        $mock->shouldReceive('maxSnapshots')->andReturn(2);

        $this->assertCount(0, $this->files->getFiles($config->reportingDirectory()));
        $this->makeSnapshot($e = new \Error("Error1"), ['config' => $mock])->report();
        $this->assertCount(1, $this->files->getFiles($config->reportingDirectory()));
        $this->makeSnapshot($e = new \Exception("Error2"), ['config' => $mock])->report();
        $this->assertCount(2, $this->files->getFiles($config->reportingDirectory()));
        $this->makeSnapshot($e = new \ErrorException("Error3"), ['config' => $mock])->report();
        $this->assertCount(2, $this->files->getFiles($config->reportingDirectory()));
    }

    private function makeSnapshot($e, array $params = []): Snapshot
    {
        return $this->container->make(Snapshot::class, $params + [
                'exception' => $e
            ]
        );
    }
}

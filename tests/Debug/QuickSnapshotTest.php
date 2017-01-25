<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Debug;

use Mockery as m;
use Psr\Log\LoggerInterface;
use Spiral\Debug\QuickSnapshot;
use Spiral\Support\ExceptionHelper;
use Spiral\Tests\BaseTest;

class QuickSnapshotTest extends BaseTest
{
    public function testGetException()
    {
        $snaphot = new QuickSnapshot($e = new \Error("Error"));
        $this->assertSame($e, $snaphot->getException());
    }

    public function testGetMessage()
    {
        $snaphot = new QuickSnapshot($e = new \Error("Error"));
        $this->assertSame(ExceptionHelper::createMessage($e), $snaphot->getMessage());
    }

    public function testDescribe()
    {
        $snaphot = new QuickSnapshot($e = new \Error("Error"));
        $this->assertInternalType('array', $snaphot->describe());
    }

    public function testRender()
    {
        $snaphot = new QuickSnapshot($e = new \Error("Error"));
        $this->assertContains($e->getMessage(), $snaphot->render());
    }

    public function testToString()
    {
        $snaphot = new QuickSnapshot($e = new \Error("Error"));
        $this->assertContains($e->getMessage(), (string)$snaphot);
    }

    public function testLog()
    {
        $log = m::mock(LoggerInterface::class);
        $snaphot = new QuickSnapshot($e = new \Error("Error"), $log);
        $log->shouldReceive('error')->with($snaphot->getMessage());

        $snaphot->report();
    }
}
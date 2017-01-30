<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Modules;

use Spiral\Tests\BaseTest;

class PublishCommandTest extends BaseTest
{
    public function testPublishUndefined()
    {
        $this->assertContains(
            'Class \'Sample\ModuleModule\' is not valid module',
            $this->commands->run('publish', ['module' => 'sample/module'])->getOutput()->fetch()
        );
    }

    public function testPublishInvalid()
    {
        $this->assertContains(
            'Class \'TestApplication\TestModule\' is not valid module',
            $this->commands->run('publish',
                ['module' => 'test-application/test'])->getOutput()->fetch()
        );
    }


    public function testPublishEmpty()
    {
        $this->assertSame(
            0,
            $this->commands->run('publish', ['module' => 'test-application/empty'])->getCode()
        );
    }
}
<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Modules;

use Spiral\Tests\BaseTest;

class RegisterCommandTest extends BaseTest
{
    public function testPublishUndefined()
    {
        $this->assertContains(
            'Class \'Sample\ModuleModule\' is not valid module',
            $this->commands->run('register', ['module' => 'sample/module'])->getOutput()->fetch()
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

    public function testRegisterEmpty()
    {
        $this->assertSame(
            0,
            $this->commands->run('register', ['module' => 'test-application/empty'])->getCode()
        );
    }
}
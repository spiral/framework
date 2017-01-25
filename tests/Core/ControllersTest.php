<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Core\Core;
use Spiral\Tests\BaseTest;
use Spiral\Tests\Core\Fixtures\SharedComponent;
use TestApplication\Controllers\DummyController;

class ControllersTest extends BaseTest
{
    public function testCallAction()
    {
        $this->assertSame("Hello, Antony.",
            $this->app->callAction(DummyController::class, 'index', [
                'name' => 'Antony'
            ])
        );
    }

    public function testCallActionDefaultParameter()
    {
        $this->assertSame("Hello, John.",
            $this->app->callAction(DummyController::class, 'index')
        );
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ControllerException
     * @expectedExceptionMessage Missing/invalid parameter 'id'
     */
    public function testCallActionMissingParameter()
    {
        $this->app->callAction(DummyController::class, 'required');
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ControllerException
     * @expectedExceptionMessage Missing/invalid parameter 'id'
     */
    public function testCallActionInvalidParameter()
    {
        $this->app->callAction(DummyController::class, 'required', ['id' => null]);
    }

    public function testCallActionDefaultAction()
    {
        $this->assertSame("Hello, Antony.",
            $this->app->callAction(DummyController::class, null, [
                'name' => 'Antony'
            ])
        );
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ControllerException
     * @expectedExceptionMessage No such controller 'Spiral\Tests\Core\DummyController2' found
     */
    public function testCallWrongController()
    {
        $this->assertSame("Hello, Antony.",
            $this->app->callAction(DummyController2::class, null, [
                'name' => 'Antony'
            ])
        );
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ControllerException
     * @expectedExceptionMessage No such action 'missing'
     */
    public function testCallWrongController2()
    {
        $this->app->callAction(DummyController::class, 'missing', [
            'name' => 'Antony'
        ]);
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ControllerException
     * @expectedExceptionMessage Action 'inner' can not be executed
     */
    public function testCallWrongController3()
    {
        $this->app->callAction(DummyController::class, 'inner');
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ControllerException
     * @expectedExceptionMessage No such controller 'Spiral\Core\Core' found
     */
    public function testCallWrongController4()
    {
        $this->assertSame("Hello, Antony.",
            $this->app->callAction(Core::class, null, [
                'name' => 'Antony'
            ])
        );
    }

    public function testBypassCore()
    {
        $controller = new DummyController();
        $this->assertSame(
            "Hello, Antony.",
            $controller->callAction('index', ['name' => 'Antony'])
        );
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     * @expectedExceptionMessage Controller can only be executed in a valid container scope
     */
    public function testBypassCoreButBrokenScope()
    {
        SharedComponent::shareContainer(null);

        $controller = new DummyController();
        $this->assertSame(
            "Hello, Antony.",
            $controller->callAction('index', ['name' => 'Antony'])
        );
    }
}
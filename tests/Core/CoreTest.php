<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Core;
use Spiral\Tests\BaseTest;
use Spiral\Tests\Core\Fixtures\SharedComponent;
use TestApplication\Controllers\DummyController;
use Zend\Diactoros\ServerRequest;

class CoreTest extends BaseTest
{
    public function testInstance()
    {
        $this->assertInstanceOf(Core::class, $this->app);
    }

    public function testShortcut()
    {
        $this->assertSame($this->app->container, spiral('container'));
    }

    public function testScoping()
    {
        $request = new ServerRequest();

        $this->assertFalse($this->container->hasInstance(ServerRequestInterface::class));
        $this->assertSame(
            spl_object_hash($request),
            $this->app->callAction(DummyController::class, 'scoped', [], [
                ServerRequestInterface::class => $request
            ])
        );
        $this->assertFalse($this->container->hasInstance(ServerRequestInterface::class));
    }

    public function testSharedContainer()
    {
        $this->assertSame(Core::sharedContainer(), $this->app->container);
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     */
    public function testSharedContainerMissing()
    {
        SharedComponent::shareContainer(null);
        Core::sharedContainer();
    }
}
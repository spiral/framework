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

    public function testDirectories()
    {
        $this->assertSame($this->app->directory('application'), directory('application'));

        $this->assertFalse($this->app->hasDirectory('custom'));
        $this->app->setDirectory('custom', __DIR__);
        $this->assertTrue($this->app->hasDirectory('custom'));

        $this->assertSame($this->app->directory('custom'), directory('custom'));
        $this->assertArrayHasKey('custom', $this->app->getDirectories());
    }

    public function testTimezone()
    {
        $this->assertSame('UTC', $this->app->getTimezone()->getName());
        $this->app->setTimezone('Europe/Minsk');
        $this->assertSame('Europe/Minsk', $this->app->getTimezone()->getName());
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\CoreException
     */
    public function testBadTimezone()
    {
        $this->app->setTimezone('magic');
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
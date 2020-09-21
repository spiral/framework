<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Core\Container;
use Spiral\Http\Diactoros\UriFactory;
use Spiral\Http\Pipeline;
use Spiral\Router\RouteGroup;
use Spiral\Router\Router;
use Spiral\Router\Target\AbstractTarget;
use Spiral\Router\Target\Action;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\Stub\AnnotatedRoutesTestCore;
use Spiral\Tests\Router\Stub\TestMiddleware;

class AnnotatedRoutesGroupTest extends TestCase
{
    public function testCoreString(): void
    {
        $router = new Router('/', new UriHandler(new UriFactory()), new Container());
        $group = new RouteGroup(new Container(), $router, new Pipeline(new Container()));

        $group->setCore(AnnotatedRoutesTestCore::class);

        $r = $group->createRoute('/', 'controller', 'method');
        $t = $this->getProperty($r, 'target');

        $this->assertInstanceOf(Action::class, $t);

        $this->assertSame('controller', $this->getProperty($t, 'controller'));
        $this->assertSame('method', $this->getProperty($t, 'action'));

        $this->assertInstanceOf(AnnotatedRoutesTestCore::class, $this->getActionProperty($t, 'core'));
    }

    public function testCoreObject(): void
    {
        $router = new Router('/', new UriHandler(new UriFactory()), new Container());
        $group = new RouteGroup(new Container(), $router, new Pipeline(new Container()));

        $group->setCore(new AnnotatedRoutesTestCore(new Container()));

        $r = $group->createRoute('/', 'controller', 'method');
        $t = $this->getProperty($r, 'target');

        $this->assertInstanceOf(Action::class, $t);

        $this->assertSame('controller', $this->getProperty($t, 'controller'));
        $this->assertSame('method', $this->getProperty($t, 'action'));

        $this->assertInstanceOf(AnnotatedRoutesTestCore::class, $this->getActionProperty($t, 'core'));
    }

    public function testMiddleware(): void
    {
        $router = new Router('/', new UriHandler(new UriFactory()), new Container());
        $group = new RouteGroup(new Container(), $router, new Pipeline(new Container()));
        $group->addMiddleware(TestMiddleware::class);

        $r = $group->createRoute('/', 'controller', 'method');

        $rl = new \ReflectionObject($r);
        $m = $rl->getMethod('makePipeline');
        $m->setAccessible(true);

        $p = $m->invoke($r);
        $m = $this->getProperty($p, 'middleware');

        $this->assertCount(1, $m);
        $this->assertInstanceOf(TestMiddleware::class, $m[0]);
    }

    /**
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws \ReflectionException
     */
    private function getProperty(object $object, string $property)
    {
        $r = new \ReflectionObject($object);
        $p = $r->getProperty($property);
        $p->setAccessible(true);

        return $p->getValue($object);
    }

    /**
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws \ReflectionException
     */
    private function getActionProperty(object $object, string $property)
    {
        $r = new \ReflectionClass(AbstractTarget::class);
        $p = $r->getProperty($property);
        $p->setAccessible(true);

        return $p->getValue($object);
    }
}

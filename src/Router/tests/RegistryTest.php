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
use Spiral\Router\GroupRegistry;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Router\UriHandler;

class RegistryTest extends TestCase
{
    public function testSameGroup(): void
    {
        $registry = new GroupRegistry($c = new Container());
        $router = new Router('/', new UriHandler(new UriFactory()), new Container());
        $c->bind(RouterInterface::class, $router);

        $group = $registry->getGroup('default');
        $this->assertSame($group, $registry->getGroup('default'));
    }
}

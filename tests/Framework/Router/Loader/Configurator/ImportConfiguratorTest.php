<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Router\Loader\Configurator;

use Spiral\App\Bootloader\RoutesBootloader;
use Spiral\Router\GroupRegistry;
use Spiral\Router\RouterInterface;
use Spiral\Tests\Framework\BaseTest;

final class ImportConfiguratorTest extends BaseTest
{
    public function testImport(): void
    {
        $group = $this->getContainer()->get(GroupRegistry::class)->getGroup('api');
        $router = $this->getContainer()->get(RouterInterface::class);

        $ref = new \ReflectionObject($group);

        /** import in @see RoutesBootloader */
        $this->assertSame(3, \count($ref->getProperty('routes')->getValue($group)));

        // routes with prefix
        $this->assertSame('/api/test-import', (string) $router->getRoute('test-import-index')->uri());
        $this->assertSame('/api/test-import/posts', (string) $router->getRoute('test-import-posts')->uri());
        $this->assertSame('/api/test-import/post/5', (string) $router->getRoute('test-import-post')->uri(['id' => 5]));
    }
}

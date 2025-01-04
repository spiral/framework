<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Router\Loader\Configurator;

use Spiral\App\Bootloader\RoutesBootloader;
use Spiral\Router\GroupRegistry;
use Spiral\Router\RouterInterface;
use Spiral\Tests\Framework\BaseTestCase;

final class ImportConfiguratorTest extends BaseTestCase
{
    public function testImport(): void
    {
        $group = $this->getContainer()->get(GroupRegistry::class)->getGroup('api');
        $router = $this->getContainer()->get(RouterInterface::class);

        $ref = new \ReflectionObject($group);

        /** import in @see RoutesBootloader */
        self::assertCount(3, $ref->getProperty('routes')->getValue($group));

        // routes with prefix and name prefix
        self::assertSame('/api/test-import', (string) $router->getRoute('api.test-import-index')->uri());
        self::assertSame('/api/test-import/posts', (string) $router->getRoute('api.test-import-posts')->uri());
        self::assertSame('/api/test-import/post/5', (string) $router->getRoute('api.test-import-post')->uri(['id' => 5]));
    }

    public function testImportWithoutNamePrefix(): void
    {
        $group = $this->getContainer()->get(GroupRegistry::class)->getGroup('other');
        $router = $this->getContainer()->get(RouterInterface::class);

        $ref = new \ReflectionObject($group);

        /** import in @see RoutesBootloader */
        self::assertCount(3, $ref->getProperty('routes')->getValue($group));

        // routes with prefix and name prefix
        self::assertSame('/other/test-import', (string) $router->getRoute('test-import-index')->uri());
        self::assertSame('/other/test-import/posts', (string) $router->getRoute('test-import-posts')->uri());
        self::assertSame('/other/test-import/post/5', (string) $router->getRoute('test-import-post')->uri(['id' => 5]));
    }
}

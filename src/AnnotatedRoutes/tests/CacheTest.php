<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

/**
 * @requires function \Spiral\Framework\Kernel::init
 */
class CacheTest extends TestCase
{
    private $app;

    public function setUp(): void
    {
        parent::setUp();

        $this->app = $this->makeApp(['DEBUG' => false]);
    }

    public function testCache(): void
    {
        $this->assertFileExists(__DIR__ . '/App/runtime/cache/routes.php');
        $this->assertCount(2, include __DIR__ . '/App/runtime/cache/routes.php');

        $this->app->getConsole()->run('route:reset');

        $this->assertSame(null, include __DIR__ . '/App/runtime/cache/routes.php');
    }
}

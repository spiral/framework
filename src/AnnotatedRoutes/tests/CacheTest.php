<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

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
        $cache = __DIR__ . '/App/runtime/cache/routes.php';
        $this->assertFileExists($cache);

        $this->assertIsIterable($this->include($cache));
        $this->assertCount(2, $this->include($cache));


        $cli = $this->app->getConsole();
        $cli->run('route:reset');

        $this->assertNull($this->include($cache));
    }

    /**
     * @param string $file
     * @return mixed
     */
    private function include(string $file)
    {
        // Required when "opcache.cli_enabled=1"
        if (\function_exists('\\opcache_invalidate')) {
            \opcache_invalidate($file);
        }

        return require $file;
    }
}

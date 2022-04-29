<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\Environment;
use Spiral\App\TestApp;

abstract class BaseTest extends TestCase
{
    public function makeApp(array $env = []): TestApp
    {
        return TestApp::create([
            'root'    => __DIR__ . '/../..',
            'app'     => __DIR__ . '/../app',
            'runtime' => sys_get_temp_dir() . '/spiral',
            'cache'   => sys_get_temp_dir() . '/spiral',
        ], false)->run(new Environment($env));
    }
}

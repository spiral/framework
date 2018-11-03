<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use PHPUnit\Framework\TestCase;
use Spiral\App\TestApp;
use Spiral\Boot\Environment;

abstract class BaseTest extends TestCase
{
    public function makeApp(array $env = []): TestApp
    {
        return TestApp::init([
            'root'    => __DIR__ . '/../..',
            'app'     => __DIR__ . '/../App',
            'runtime' => sys_get_temp_dir() . '/spiral',
            'cache'   => sys_get_temp_dir() . '/spiral',
        ], new Environment($env), false);
    }
}
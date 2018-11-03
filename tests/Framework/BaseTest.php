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
    public function makeApp(array $env): TestApp
    {
        return TestApp::init([
            'root' => __DIR__ . '/../App/'
        ], new Environment($env), false);
    }
}
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
use Spiral\Files\Files;

abstract class BaseTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();

        $fs = new Files();
        foreach ($fs->getFiles(__DIR__ . '/../app/migrations') as $f) {
            $fs->delete($f);
        }
    }

    public function makeApp(array $env = []): TestApp
    {
        return TestApp::init([
            'root'    => __DIR__ . '/../..',
            'app'     => __DIR__ . '/../app',
            'runtime' => sys_get_temp_dir() . '/spiral',
            'cache'   => sys_get_temp_dir() . '/spiral',
        ], new Environment($env), false);
    }
}
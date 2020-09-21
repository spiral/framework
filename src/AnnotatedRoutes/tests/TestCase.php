<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Router;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Spiral\Boot\Environment;
use Spiral\Tests\Router\App\App;

/**
 * @requires function \Spiral\Framework\Kernel::init
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param array $env
     * @return App
     * @throws \Throwable
     */
    protected function makeApp(array $env): App
    {
        $config = [
            'root' => __DIR__ . '/App',
            'app'  => __DIR__ . '/App',
        ];

        return App::init($config, new Environment($env), false);
    }
}

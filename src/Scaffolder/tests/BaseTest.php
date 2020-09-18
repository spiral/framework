<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder;

use PHPUnit\Framework\TestCase;
use Spiral\Tests\Scaffolder\App\TestApp;
use Throwable;

abstract class BaseTest extends TestCase
{
    /** @var TestApp */
    protected $app;

    /**
     * @throws Throwable
     */
    public function setUp(): void
    {
        $this->app = TestApp::init([
            'root' => __DIR__ . '/App',
        ], null, false);
    }
}

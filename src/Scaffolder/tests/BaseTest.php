<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder;

use PHPUnit\Framework\TestCase;
use Spiral\Tests\Scaffolder\App\TestApp;
use Throwable;

/**
 * @requires function \Spiral\Framework\Kernel::init
 */
abstract class BaseTest extends TestCase
{
    /** @var TestApp */
    protected $app;

    /**
     * @throws Throwable
     */
    public function setUp(): void
    {
        $this->app = TestApp::create([
            'root' => __DIR__ . '/App',
        ], false)->run();
    }
}

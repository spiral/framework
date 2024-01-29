<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Nyholm\Bootloader\NyholmBootloader;
use Spiral\Router\Bootloader\AnnotatedRoutesBootloader;
use Spiral\Testing\TestCase as BaseTestCase;

/**
 * @requires function \Spiral\Framework\Kernel::init
 */
abstract class TestCase extends BaseTestCase
{
    public function defineBootloaders(): array
    {
        return [
            NyholmBootloader::class,
            AnnotatedRoutesBootloader::class,
        ];
    }

    public function rootDirectory(): string
    {
        return __DIR__;
    }

    public function defineDirectories(string $root): array
    {
        return \array_merge(parent::defineDirectories($root), ['app' => $root . '/App']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cleanUpRuntimeDirectory();
    }
}

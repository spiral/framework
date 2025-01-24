<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\BootloadManager\ClassesRegistry;
use Spiral\Boot\BootloadManager\InitializerInterface;

final class CustomInitializer implements InitializerInterface
{
    public function init(array $classes): \Generator
    {
        yield 'foo' => ['bootloader' => new BootloaderA(), 'options' => []];
    }

    public function getRegistry(): ClassesRegistry {}
}

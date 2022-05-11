<?php

declare(strict_types=1);

namespace Spiral\Validation\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\ValidationProvider;
use Spiral\Validation\ValidationProviderInterface;

final class ValidationBootloader extends Bootloader implements SingletonInterface
{
    protected const SINGLETONS = [
        ValidationProviderInterface::class => ValidationProvider::class,
    ];
}

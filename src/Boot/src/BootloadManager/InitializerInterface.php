<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Boot\BootloadManagerInterface;

/**
 * @psalm-import-type TClass from BootloadManagerInterface
 */
interface InitializerInterface
{
    /**
     * Instantiate bootloader objects and resolve dependencies
     *
     * @param TClass[]|array<class-string<BootloaderInterface>, array<string,mixed>> $classes
     */
    public function init(array $classes): \Generator;

    public function getRegistry(): ClassesRegistry;
}

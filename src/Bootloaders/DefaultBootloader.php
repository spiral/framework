<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Bootloaders;

use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Core\MemoryInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Framework\Memory;

class DefaultBootloader extends Bootloader
{
    const SINGLETONS = [
        FilesInterface::class  => Files::class,
        MemoryInterface::class => Memory::class
    ];
}
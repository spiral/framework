<?php

declare(strict_types=1);

namespace Spiral\Boot\Exception;

use Spiral\Boot\BootloadManagerInterface;

/**
 * @psalm-import-type TClass from BootloadManagerInterface
 */
final class BootloaderAlreadyBootedException extends BootException
{
    /**
     * @psalm-param TClass $bootloader
     */
    public function __construct(string $bootloader)
    {
        parent::__construct(\sprintf('The Bootloader [%s] already booted.', $bootloader));
    }
}

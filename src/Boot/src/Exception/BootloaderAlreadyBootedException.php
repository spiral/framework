<?php

declare(strict_types=1);

namespace Spiral\Boot\Exception;

final class BootloaderAlreadyBootedException extends BootException
{
    /**
     * @psalm-param class-string $bootloader
     */
    public function __construct(string $bootloader)
    {
        parent::__construct(\sprintf('The Bootloader [%s] already booted.', $bootloader));
    }
}

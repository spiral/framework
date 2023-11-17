<?php

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

/**
 * @psalm-import-type TClass from \Spiral\Boot\BootloadManagerInterface
 */
final class BootloaderRegistry implements BootloaderRegistryInterface
{
    /**
     * @param array<TClass>|array<TClass, array<string, mixed>> $systemBootloaders
     * @param array<TClass>|array<TClass, array<string, mixed>> $bootloaders
     */
    public function __construct(
        private array $systemBootloaders = [],
        private array $bootloaders = [],
    ) {
    }

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     */
    public function registerSystem(string|array $bootloader): void
    {
        if ($this->hasBootloader($bootloader)) {
            return;
        }

        \is_string($bootloader)
            ? $this->systemBootloaders[] = $bootloader
            : $this->systemBootloaders[\array_key_first($bootloader)] = $bootloader[\array_key_first($bootloader)]
        ;
    }

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     */
    public function register(string|array $bootloader): void
    {
        if ($this->hasBootloader($bootloader)) {
            return;
        }

        \is_string($bootloader)
            ? $this->bootloaders[] = $bootloader
            : $this->bootloaders[\array_key_first($bootloader)] = $bootloader[\array_key_first($bootloader)]
        ;
    }

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getSystemBootloaders(): array
    {
        return $this->systemBootloaders;
    }

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getBootloaders(): array
    {
        return $this->bootloaders;
    }

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     */
    private function hasBootloader(string|array $bootloader): bool
    {
        if (\is_array($bootloader)) {
            return false;
        }

        return
            \in_array($bootloader, $this->systemBootloaders, true) ||
            \in_array($bootloader, $this->bootloaders, true);
    }
}

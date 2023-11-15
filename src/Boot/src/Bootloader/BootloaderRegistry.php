<?php

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

/**
 * @psalm-import-type TClass from BootloaderRegistryInterface
 */
final class BootloaderRegistry implements BootloaderRegistryInterface
{
    private const SYSTEM = 'system';
    private const LOAD = 'load';
    private const APPLICATION = 'application';

    /**
     * @param array<TClass>|array<TClass, array<string, mixed>> $system
     * @param array<TClass>|array<TClass, array<string, mixed>> $load
     * @param array<TClass>|array<TClass, array<string, mixed>> $application
     */
    public function __construct(
        private array $system = [],
        private array $load = [],
        private array $application = [],
    ) {
    }

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     */
    public function addSystemBootloader(string|array $bootloader): void
    {
        $this->addBootloader($bootloader, self::SYSTEM);
    }

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     */
    public function addLoadBootloader(string|array $bootloader): void
    {
        $this->addBootloader($bootloader, self::LOAD);
    }

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     */
    public function addApplicationBootloader(string|array $bootloader): void
    {
        $this->addBootloader($bootloader, self::APPLICATION);
    }

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getSystemBootloaders(): array
    {
        return $this->system;
    }

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getLoadBootloaders(): array
    {
        return $this->load;
    }

    /**
     * @return array<TClass>|array<TClass, array<string, mixed>>
     */
    public function getApplicationBootloaders(): array
    {
        return $this->application;
    }

    /**
     * @param TClass|array<TClass, array<string, mixed>> $bootloader
     * @param 'system'|'load'|'application' $section
     */
    private function addBootloader(string|array $bootloader, string $section): void
    {
        if (\is_string($bootloader) && \in_array($bootloader, $this->{$section}, true)) {
            return;
        }

        $this->{$section}[] = $bootloader;
    }
}

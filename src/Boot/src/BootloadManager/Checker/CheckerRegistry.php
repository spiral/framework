<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager\Checker;

final class CheckerRegistry implements CheckerRegistryInterface
{
    /**
     * @var array<BootloaderCheckerInterface>
     */
    private array $checkers = [];

    public function register(BootloaderCheckerInterface $checker): void
    {
        $this->checkers[] = $checker;
    }

    /**
     * @return array<BootloaderCheckerInterface>
     */
    public function getCheckers(): array
    {
        return $this->checkers;
    }
}

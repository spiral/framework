<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager\Checker;

use Spiral\Boot\Attribute\BootloadConfig;
use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Boot\EnvironmentInterface;

final class ConfigChecker implements BootloaderCheckerInterface
{
    public function __construct(
        private readonly EnvironmentInterface $environment,
    ) {
    }

    public function canInitialize(BootloaderInterface|string $bootloader, ?BootloadConfig $config = null): bool
    {
        if ($config === null) {
            return true;
        }

        if (!$config->enabled) {
            return false;
        }

        foreach ($config->denyEnv as $env => $denyValues) {
            $value = $this->environment->get($env);
            if ($value !== null && \in_array($value, (array) $denyValues, true)) {
                return false;
            }
        }

        foreach ($config->allowEnv as $env => $allowValues) {
            $value = $this->environment->get($env);
            if ($value === null || !\in_array($value, (array) $allowValues, true)) {
                return false;
            }
        }

        return true;
    }
}

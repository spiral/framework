<?php

declare(strict_types=1);

namespace Spiral\Validation\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Set;
use Spiral\Core\Attribute\Singleton;
use Spiral\Validation\Config\ValidationConfig;
use Spiral\Validation\Exception\ValidationException;
use Spiral\Validation\ValidationInterface;
use Spiral\Validation\ValidationProvider;
use Spiral\Validation\ValidationProviderInterface;

#[Singleton]
final class ValidationBootloader extends Bootloader
{
    protected const SINGLETONS = [
        ValidationProviderInterface::class => ValidationProvider::class,
        ValidationInterface::class => [self::class, 'initDefaultValidator'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(): void
    {
        $this->config->setDefaults(ValidationConfig::CONFIG, [
            'defaultValidator' => null,
        ]);
    }

    /**
     * @param non-empty-string $name
     */
    public function setDefaultValidator(string $name): void
    {
        if ($this->config->getConfig(ValidationConfig::CONFIG)['defaultValidator'] === null) {
            $this->config->modify(ValidationConfig::CONFIG, new Set('defaultValidator', $name));
        }
    }

    private function initDefaultValidator(
        ValidationConfig $config,
        ValidationProviderInterface $provider
    ): ValidationInterface {
        if ($config->getDefaultValidator() === null) {
            throw new ValidationException('Default Validator is not configured.');
        }

        return $provider->getValidation($config->getDefaultValidator());
    }
}

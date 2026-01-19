<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

/**
 * Attribute to configure bootloader behavior.
 *
 * This attribute allows defining configuration for bootloaders, including
 * constructor arguments, enabling/disabling based on environment variables,
 * and controlling how configuration overrides work.
 *
 * Example usage:
 * ```php
 * // Basic configuration with constructor arguments
 * #[BootloadConfig(args: ['defaultConnection' => 'sqlite'])]
 * class DatabaseBootloader extends Bootloader
 * {
 *     public function __construct(
 *         private readonly string $defaultConnection
 *     ) {}
 *
 *     // ...
 * }
 *
 * // Conditionally enable based on environment
 * #[BootloadConfig(
 *     allowEnv: ['APP_ENV' => ['local', 'development']],
 *     denyEnv: ['TESTING' => [true, 1, 'true']]
 * )]
 * class DevToolsBootloader extends Bootloader
 * {
 *     // Only loaded in local or development environments
 *     // And not when TESTING is true
 * }
 *
 * // Prevent runtime configuration from overriding attribute configuration
 * #[BootloadConfig(args: ['debug' => true], override: false)]
 * class DebugBootloader extends Bootloader
 * {
 *     // The 'debug' argument will always be true, even if different
 *     // configuration is provided at runtime
 * }
 * ```
 *
 * When a bootloader has both runtime configuration and a BootloadConfig attribute,
 * the override parameter controls which configuration takes precedence.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class BootloadConfig
{
    /**
     * @param array $args Arguments to pass to the bootloader's constructor.
     * @param bool $enabled Whether this bootloader is enabled.
     * @param array $allowEnv Environment conditions that must be satisfied for the bootloader to be enabled.
     *                        Format: ['ENV_VAR' => ['allowed_value1', 'allowed_value2']]
     * @param array $denyEnv Environment conditions that must not be satisfied for the bootloader to be enabled.
     *                       Format: ['ENV_VAR' => ['denied_value1', 'denied_value2']]
     * @param bool $override Whether runtime configuration should override the attribute configuration.
     *                       If false, attribute configuration takes precedence.
     */
    public function __construct(
        public array $args = [],
        public bool $enabled = true,
        public array $allowEnv = [],
        public array $denyEnv = [],
        public bool $override = true,
    ) {}
}

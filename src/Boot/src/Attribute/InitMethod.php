<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

/**
 * Attribute for marking methods that should be called during the initialization phase.
 *
 * Methods marked with this attribute will be called during the bootloader's
 * initialization phase, before the boot phase begins. This is where you typically
 * set up container bindings, register services, or perform other initialization tasks.
 *
 * The priority parameter determines the order in which init methods are called.
 * Higher priority values are executed first.
 *
 * Example usage:
 * ```php
 * class MyBootloader extends Bootloader
 * {
 *     // Called during initialization phase with default priority (0)
 *     #[InitMethod]
 *     public function registerServices(Container $container): void
 *     {
 *         $container->bindSingleton(MyService::class, MyServiceImplementation::class);
 *     }
 *
 *     // Called during initialization phase with high priority (10)
 *     #[InitMethod(priority: 10)]
 *     public function setupCore(): void
 *     {
 *         // Setup core components first
 *     }
 *
 *     // Called during initialization phase with low priority (-10)
 *     #[InitMethod(priority: -10)]
 *     public function setupExtensions(): void
 *     {
 *         // Setup extensions after core components
 *     }
 * }
 * ```
 *
 * Init methods are executed before any bootloader's boot methods are called.
 *
 * @see BootMethod For methods to be called during boot phase
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class InitMethod
{
    /**
     * @param int $priority The priority of this init method. Higher values are executed first.
     */
    public function __construct(
        public readonly int $priority = 0,
    ) {}
}

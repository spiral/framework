<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

/**
 * Attribute for marking methods that should be called during the boot phase.
 * 
 * Methods marked with this attribute will be called during the bootloader's
 * boot phase, after all initialization methods have been called.
 * The boot phase is where you typically configure services, register event listeners,
 * or perform other setup tasks.
 * 
 * The priority parameter determines the order in which boot methods are called.
 * Higher priority values are executed first.
 * 
 * Example usage:
 * ```php
 * class MyBootloader extends Bootloader
 * {
 *     // Called during boot phase with default priority (0)
 *     #[BootMethod]
 *     public function configureRoutes(RouterInterface $router): void
 *     {
 *         $router->addRoute('home', '/');
 *     }
 *     
 *     // Called during boot phase with high priority (10)
 *     #[BootMethod(priority: 10)]
 *     public function configureDatabase(DatabaseInterface $db): void
 *     {
 *         $db->setDefaultConnection('default');
 *     }
 *     
 *     // Called during boot phase with low priority (-10)
 *     #[BootMethod(priority: -10)]
 *     public function registerEventListeners(EventDispatcherInterface $dispatcher): void
 *     {
 *         $dispatcher->addListener(ApplicationStarted::class, fn() => $this->onStart());
 *     }
 * }
 * ```
 * 
 * Boot methods are executed after all bootloaders' init methods have been called.
 * 
 * @see InitMethod For methods to be called during initialization phase
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class BootMethod
{
    /**
     * @param int $priority The priority of this boot method. Higher values are executed first.
     */
    public function __construct(
        public readonly int $priority = 0,
    ) {}
}

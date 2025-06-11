<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

/**
 * Attribute for marking methods that provide container bindings.
 *
 * Methods marked with this attribute will be invoked each time the container
 * needs to resolve the dependency, creating a new instance each time.
 * The return value of the method will be bound to the specified alias
 * or to all interfaces/classes in the return type.
 *
 * Example usage:
 * ```php
 * class MyBootloader extends Bootloader
 * {
 *     // Method will be called each time the container resolves HttpClientInterface
 *     #[BindMethod]
 *     public function createHttpClient(): HttpClientInterface
 *     {
 *         return new HttpClient();
 *     }
 *
 *     // Method will be called each time the container resolves DbFactory
 *     // instead of its return type (DatabaseFactory)
 *     #[BindMethod(alias: DbFactory::class)]
 *     public function createDatabaseFactory(): DatabaseFactory
 *     {
 *         return new DatabaseFactory();
 *     }
 *
 *     // Method will be called each time the container resolves either
 *     // LogManagerInterface or its return type (LogManager)
 *     #[BindMethod(alias: LogManagerInterface::class, aliasesFromReturnType: true)]
 *     public function createLogManager(): LogManager
 *     {
 *         return new LogManager();
 *     }
 * }
 * ```
 *
 * This attribute is similar to defining bindings via the `defineBindings()` method,
 * but with a more expressive and type-safe approach.
 *
 * @see SingletonMethod For binding singleton instances
 * @see InjectorMethod For binding injector methods
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class BindMethod extends AbstractMethod {}

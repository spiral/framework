<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

/**
 * Attribute for marking methods that provide singleton container bindings.
 * 
 * Methods marked with this attribute will be invoked only once, and the
 * return value will be cached and reused for subsequent resolutions.
 * The return value of the method will be bound to the specified alias
 * or to all interfaces/classes in the return type.
 * 
 * Example usage:
 * ```php
 * class MyBootloader extends Bootloader
 * {
 *     // Method will be called once and the result will be cached
 *     #[SingletonMethod]
 *     public function createHttpClient(): HttpClientInterface
 *     {
 *         return new HttpClient();
 *     }
 *     
 *     // Method will be called once and the result will be bound to DbFactory
 *     // instead of its return type (DatabaseFactory)
 *     #[SingletonMethod(alias: DbFactory::class)]
 *     public function createDatabaseFactory(): DatabaseFactory
 *     {
 *         return new DatabaseFactory();
 *     }
 *     
 *     // Method will be called once and the result will be bound to both
 *     // LogManagerInterface and its return type (LogManager)
 *     #[SingletonMethod(alias: LogManagerInterface::class, aliasesFromReturnType: true)]
 *     public function createLogManager(): LogManager
 *     {
 *         return new LogManager();
 *     }
 * }
 * ```
 * 
 * This attribute is similar to defining singletons via the `defineSingletons()` method,
 * but with a more expressive and type-safe approach.
 * 
 * @see BindMethod For non-singleton bindings
 * @see InjectorMethod For binding injector methods
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class SingletonMethod extends AbstractMethod {}

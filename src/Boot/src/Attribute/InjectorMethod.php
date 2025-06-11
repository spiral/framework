<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

/**
 * Attribute for marking methods that provide a custom injector.
 *
 * Methods marked with this attribute will be used as injectors for the specified
 * alias type. An injector is responsible for creating and configuring instances
 * of a specific type when they're requested from the container.
 *
 * Unlike BindMethod and SingletonMethod which bind the return value of the method,
 * InjectorMethod binds the method itself as an injector for the specified type.
 *
 * Example usage:
 * ```php
 * class MyBootloader extends Bootloader
 * {
 *     // Method will be used as injector for LoggerInterface
 *     #[InjectorMethod(LoggerInterface::class)]
 *     public function createLogger(string $channel = 'default'): LoggerInterface
 *     {
 *         return new Logger($channel);
 *     }
 *
 *     // Method will be used as injector for ConnectionInterface
 *     #[InjectorMethod(ConnectionInterface::class)]
 *     public function createDatabaseConnection(string $name = null): ConnectionInterface
 *     {
 *         return $name === null
 *             ? new DefaultConnection()
 *             : new NamedConnection($name);
 *     }
 * }
 * ```
 *
 * With the above example, any time a LoggerInterface is requested from the container,
 * the createLogger method will be called with any provided constructor arguments.
 *
 * Injectors are powerful for types that need custom creation logic or that
 * accept additional parameters during construction.
 *
 * @see BindMethod For simple method bindings
 * @see SingletonMethod For singleton method bindings
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class InjectorMethod extends AbstractMethod
{
    /**
     * @param non-empty-string $alias The class or interface name to register this injector for.
     */
    public function __construct(string $alias)
    {
        parent::__construct($alias);
    }
}

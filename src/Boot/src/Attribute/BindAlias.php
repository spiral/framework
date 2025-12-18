<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

/**
 * Attribute to define additional aliases for a method.
 * 
 * This attribute allows defining multiple aliases for a method in a bootloader class.
 * It can be used to bind a method's return value to multiple interface or class names.
 * 
 * This attribute can be applied multiple times to the same method.
 * 
 * Example usage:
 * ```php
 * class MyBootloader extends Bootloader
 * {
 *     #[BindAlias(LoggerInterface::class, PsrLoggerInterface::class)]
 *     #[BindAlias(MonologLoggerInterface::class)]
 *     public function createLogger(): Logger
 *     {
 *         return new Logger();
 *     }
 * }
 * ```
 * 
 * The above example binds the returned Logger instance to LoggerInterface, 
 * PsrLoggerInterface, and MonologLoggerInterface.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class BindAlias
{
    /**
     * @param non-empty-string[] $aliases List of class or interface names to bind the returned value to.
     */
    public readonly array $aliases;

    /**
     * @param non-empty-string ...$aliases List of class or interface names to bind the returned value to.
     */
    public function __construct(string ...$aliases)
    {
        $this->aliases = $aliases;
    }
}

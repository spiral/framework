<?php

declare(strict_types=1);

namespace Spiral\Boot\Attribute;

/**
 * Attribute to bind a method's result to a specific container scope.
 *
 * This attribute allows defining a specific scope for the binding created
 * by method attributes such as BindMethod or SingletonMethod.
 *
 * Example usage:
 * ```php
 * class MyBootloader extends Bootloader
 * {
 *     // Bind to the 'http' scope
 *     #[BindMethod]
 *     #[BindScope('http')]
 *     public function createHttpClient(): HttpClientInterface
 *     {
 *         return new HttpClient();
 *     }
 *
 *     // Bind to the 'console' scope using an enum
 *     #[SingletonMethod]
 *     #[BindScope(ScopeEnum::Console)]
 *     public function createConsoleOutput(): OutputInterface
 *     {
 *         return new ConsoleOutput();
 *     }
 * }
 * ```
 *
 * When using a scope, the binding will only be available when the container
 * is running within that scope.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class BindScope
{
    /**
     * The scope name to bind to.
     */
    public readonly string $scope;

    /**
     * @param string|\BackedEnum $scope The scope name or enum value to bind to.
     *                                  If an enum is provided, its value will be used as the scope name.
     */
    public function __construct(string|\BackedEnum $scope)
    {
        $this->scope = \is_object($scope) ? (string) $scope->value : $scope;
    }
}

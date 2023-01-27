<?php

declare(strict_types=1);

namespace Spiral\Core;

use Fiber;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Scope class provides ability to enable or disable global container access within specific access scope.
 *
 * @internal
 */
final class ContainerScope
{
    private static ?ContainerInterface $container = null;

    /**
     * Returns currently active container scope if any.
     */
    public static function getContainer(): ?ContainerInterface
    {
        return self::$container;
    }

    /**
     * Invokes given closure or function withing global IoC scope.
     *
     * @throws Throwable
     */
    public static function runScope(ContainerInterface $container, callable $scope): mixed
    {
        [$previous, self::$container] = [self::$container, $container];

        try {
            if (Fiber::getCurrent() === null) {
                return $scope(self::$container);
            }

            // Wrap scope into fiber
            $fiber = new Fiber(static fn () => $scope(self::$container));
            $value = $fiber->start();
            while (!$fiber->isTerminated()) {
                self::$container = $previous;
                $resume = Fiber::suspend($value);
                self::$container = $container;
                $value = $fiber->resume($resume);
            }
            return $fiber->getReturn();
        } finally {
            self::$container = $previous;
        }
    }
}

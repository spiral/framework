<?php

declare(strict_types=1);

namespace Spiral\Core;

use Fiber;
use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Internal\Proxy;
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
        if (Proxy::isProxy($container)) {
            // Ignore Proxy to avoid recursion
            $container = $previous = self::$container ?? throw new ContainerException('Proxy is out of scope.');
        } else {
            [$previous, self::$container] = [self::$container, $container];
        }

        try {
            if (Fiber::getCurrent() === null) {
                return $scope(self::$container);
            }

            // Wrap scope into fiber
            $fiber = new Fiber(static fn () => $scope(self::$container));
            $value = $fiber->start();
            while (!$fiber->isTerminated()) {
                self::$container = $previous;
                try {
                    $resume = Fiber::suspend($value);
                } catch (Throwable $e) {
                    self::$container = $container;
                    $value = $fiber->throw($e);
                    continue;
                }

                self::$container = $container;
                $value = $fiber->resume($resume);
            }
            return $fiber->getReturn();
        } finally {
            self::$container = $previous;
        }
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Events;

use Spiral\Core\ContainerScope;
use Spiral\Core\InvokerInterface;

final class AutowireListenerFactory implements ListenerFactoryInterface
{
    public function create(string|object $listener, string $method): \Closure
    {
        if ($this->listenerShouldBeAutowired($listener, $method)) {
            return static function (object $event) use ($listener, $method): void {
                ContainerScope::getContainer()
                    ->get(InvokerInterface::class)
                    ->invoke([self::getListener($listener), $method], ['event' => $event]);
            };
        }

        return static function (object $event) use ($listener, $method): void {
            self::getListener($listener)->{$method}($event);
        };
    }

    /**
     * @template TClass
     * @param class-string<TClass>|TClass $listener
     *
     * @return TClass
     */
    private static function getListener(string|object $listener): object
    {
        if (\is_object($listener)) {
            return $listener;
        }

        return ContainerScope::getContainer()->get($listener);
    }

    private function listenerShouldBeAutowired(object|string $listener, string $method): bool
    {
        if (!\is_object($listener) && !\class_exists($listener) && !\interface_exists($listener)) {
            return true;
        }

        try {
            $refMethod = new \ReflectionMethod($listener, $method);

            return $refMethod->getNumberOfParameters() > 1;
        } catch (\ReflectionException $e) {
            $listener = \is_object($listener) ? $listener::class : $listener;

            throw match (true) {
                \interface_exists($listener) => new \BadMethodCallException(
                    \sprintf('Listener interface `%s` does not contain `%s` method.', $listener, $method),
                    previous: $e
                ),
                default => new \BadMethodCallException(
                    \sprintf('Listener `%s` does not contain `%s` method.', $listener, $method),
                    previous: $e
                )
            };
        }
    }
}

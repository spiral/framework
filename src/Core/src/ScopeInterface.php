<?php

declare(strict_types=1);

namespace Spiral\Core;

/**
 * Provides ability to run code withing isolated IoC scope.
 */
interface ScopeInterface
{
    /**
     * Invokes given closure or function withing specific IoC scope.
     *
     * Example:
     *
     * $container->run(['actor' => new Actor()], function() use($container) {
     *    dump($container->get('actor'));
     * });
     *
     * @throws \Throwable
     */
    public function runScope(array $bindings, callable $scope): mixed;
}

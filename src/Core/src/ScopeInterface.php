<?php

declare(strict_types=1);

namespace Spiral\Core;

/**
 * Provides ability to run code withing isolated IoC scope.
 *
 * @method mixed runScope(array|Scope $bindings, callable $scope)
 */
interface ScopeInterface
{
    /**
     * Invokes given closure or function withing specific IoC scope.
     *
     * Run the closure withing specific IoC scope.
     * The method operates in two modes depending on the arguments passed.
     *
     * 1. Simple mode (deprecated).
     *    Activated when bindings are passed as an array.
     *    The closure is run with the same container, but with new bindings.
     *    Upon completion of the closure, the bindings overridden at the beginning are restored.
     *    This scope mode is not safe in asynchronous code, there is no control and isolation of scopes.
     *    Note that this mode will be removed in the future.
     *
     *    ```php
     *    $container->runScope(['actor' => new Actor()], function() use($container) {
     *        dump($container->get('actor'));
     *    });
     *    ```
     *
     * 2. Extended mode. Activated when passing {@see Scope} as the first parameter.
     *    Before launching the closure, a new container is created, which is embedded in the scope hierarchy.
     *    The scope can be named or unnamed. What's important is that each separated container has
     *    its own set of bindings, its own cache, and when the associated scope is destroyed,
     *    the entire container cache will be cleared, and the container will be destroyed.
     *    This mode is safe when working in an asynchronous application.
     *
     *    ```php
     *    $container->runScope(
     *        new Scope(bindings: ['actor' => new Actor()]),
     *        function(ContainerInterface $container) {
     *            dump($container->get('actor'));
     *        }
     *    );
     *    ```
     *
     * @throws \Throwable
     */
    public function runScope(array $bindings, callable $scope): mixed;
}

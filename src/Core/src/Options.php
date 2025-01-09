<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Binder\SingletonOverloadException;
use Spiral\Core\Exception\Scope\BadScopeException;

class Options
{
    /**
     * Check that object is created in the correct scope.
     * The scope is defined by the {@see \Spiral\Core\Attribute\Scope} attribute.
     * If the check is enabled and the object is created outside
     * the required scope, an exception {@see BadScopeException} will be thrown.
     *
     * Will be set to true by default since version 4.0
     */
    public bool $checkScope = false;

    /**
     * Validate resolved arguments in Container services, for example in {@see FactoryInterface::make()},
     * {@see InjectorInterface::invoke()} and {@see ConfigsInterface::get()}.
     * This occurs when the {@see ResolverInterface::resolveArguments()} resolves parameters
     * from the Container or predefined arguments.
     */
    public bool $validateArguments = true;

    /**
     * Force rebind of singletons in the Container. If set to false, the Container will throw
     * an exception {@see SingletonOverloadException} if you try to rebind a singleton after it has been resolved.
     * This option will be used only if the `force` argument in {@see Container::bindSingleton()} is null.
     *
     * Will be set to false by default since version 4.0
     */
    public bool $allowSingletonsRebinding = true;
}

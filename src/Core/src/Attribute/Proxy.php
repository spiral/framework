<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

/**
 * The attribute is specified on a parameter that should be resolved from the container.
 * The parameter type MUST be an interface. When resolved by the container,
 * a generated proxy object of the specified interface will be passed as an argument,
 * which will redirect method calls to the real object obtained from the container.
 *
 * @internal We are testing this feature, it may be changed in the future.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class Proxy implements Plugin
{
    /**
     * @param bool $attach Attach the container to the proxy object.
     *        If TRUE, the same container that created the proxy object will be used when accessing the proxy object.
     *        If FALSE, the container of the current executing context (scope) will be used.
     * @internal
     */
    public bool $attachContainer = false;

    /**
     * @param bool $proxyOverloads Include `__call` and `__callStatic` methods in proxy to redirect method calls that
     *        are not defined in the interface.
     * @internal
     */
    public bool $proxyOverloads = false;
}

<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

/**
 * The attribute is specified on a parameter that should be resolved from the container.
 * The parameter type MUST be an interface. When resolved by the container,
 * a generated proxy object of the specified interface will be passed as an argument,
 * which will redirect method calls to the real object obtained from the container.
 *
 *
 *
 * @internal We are testing this feature, it may be changed in the future.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class Proxy implements Plugin
{
    /**
     * @param bool $magicCall Generate `__call` and `__callStatic` methods in proxy.
     */
    public function __construct(
        public bool $magicCall = false,
    ) {
    }
}

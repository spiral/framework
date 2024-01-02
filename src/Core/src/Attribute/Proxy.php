<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

/**
 * Scoped proxy
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

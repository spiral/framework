<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\Container\ContainerException;

/**
 * @internal
 */
trait ProxyTrait
{
    private static string $__container_proxy_alias;
    private \Stringable|string|null $__container_proxy_context = null;
}

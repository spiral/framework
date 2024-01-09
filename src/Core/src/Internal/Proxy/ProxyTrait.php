<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

use Psr\Container\ContainerInterface;

/**
 * @internal
 */
trait ProxyTrait
{
    private static string $__container_proxy_alias;
    private \Stringable|string|null $__container_proxy_context = null;
    private ?ContainerInterface $__container_proxy_container = null;
}

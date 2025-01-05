<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

/**
 * @internal
 * @template T of object
 * @template TBootloader of object
 */
interface AttributeResolverInterface
{
    /**
     * @psalm-param T $attribute
     * @psalm-param TBootloader $service
     * @throws \ReflectionException
     */
    public function resolve(object $attribute, object $service, \ReflectionMethod $method): void;
}
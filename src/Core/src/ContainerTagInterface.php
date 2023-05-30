<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerInterface;

/**
 * Provides ability to get all bindings by tag.
 *
 * @internal We are testing this feature, it may be changed in the future.
 */
interface ContainerTagInterface extends ContainerInterface
{
    /**
     * @template T
     *
     * @param class-string<T>|string $tag
     * @param bool $resolve If {@see false} then resolved values (singletons) will be returned.
     *
     * @return ($tag is class-string ? T[] : array)
     */
    public function getTag(string $tag, bool $resolve = true): array;
}

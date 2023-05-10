<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * Factory that can be resolved later.
 */
final class DeferredFactory extends Binding
{
    /**
     * @param array{0: object|non-empty-string, 1: non-empty-string, ...} $factory
     */
    public function __construct(
        public readonly array $factory,
        public readonly bool $singleton = false,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Reactor;

/**
 * Provides ability to replace strings within the content and all inner declarations.
 */
interface ReplaceableInterface
{
    /**
     * Replace sub string in element content.
     */
    public function replace(array|string $search, array|string $replace): mixed;
}

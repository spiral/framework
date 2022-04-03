<?php

declare(strict_types=1);

namespace Spiral\Storage;

/**
 * File visibility enum type.
 *
 * @psalm-type VisibilityType = Visibility::VISIBILITY_*
 */
interface Visibility
{
    /**
     * @var string
     * @psalm-var VisibilityType
     */
    public const VISIBILITY_PUBLIC = \League\Flysystem\Visibility::PUBLIC;

    /**
     * @var string
     * @psalm-var VisibilityType
     */
    public const VISIBILITY_PRIVATE = \League\Flysystem\Visibility::PRIVATE;
}

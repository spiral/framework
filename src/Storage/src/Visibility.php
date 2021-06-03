<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

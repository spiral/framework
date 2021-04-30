<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Config\DTO\FileSystemInfo;

interface ClassBasedInterface
{
    public const CLASS_KEY = 'class';

    public function getClass(): string;
}

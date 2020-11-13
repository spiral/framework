<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

use Spiral\Attributes\Exception\NotFoundException;

interface ManagerInterface
{
    /**
     * @final
     * @var string
     */
    public const DEFAULT_READER = 'default';

    /**
     * @param string $name
     * @return ReaderInterface
     * @throws NotFoundException
     */
    public function get(string $name = self::DEFAULT_READER): ReaderInterface;
}

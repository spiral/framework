<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Doctrine;

use Doctrine\Common\Annotations\Reader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Attributes\ResolverInterface;

class DoctrineResolver implements ResolverInterface
{
    /**
     * {@inheritDoc}
     */
    public function isSupported(): bool
    {
        return \interface_exists(Reader::class);
    }

    /**
     * {@inheritDoc}
     */
    public function create(): ReaderInterface
    {
        return new DoctrineReader();
    }
}

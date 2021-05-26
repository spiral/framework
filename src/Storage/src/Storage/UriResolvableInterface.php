<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Storage;

use Spiral\Distribution\ResolverInterface;

interface UriResolvableInterface
{
    /**
     * @return ResolverInterface|null
     */
    public function getUriResolver(): ?ResolverInterface;

    /**
     * @param ResolverInterface|null $resolver
     * @return $this
     */
    public function withUriResolver(?ResolverInterface $resolver): self;
}

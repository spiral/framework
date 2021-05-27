<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Bucket;

use Spiral\Distribution\UriResolverInterface;

interface UriResolvableInterface
{
    /**
     * @return UriResolverInterface|null
     */
    public function getUriResolver(): ?UriResolverInterface;

    /**
     * @param UriResolverInterface|null $resolver
     * @return $this
     */
    public function withUriResolver(?UriResolverInterface $resolver): self;
}

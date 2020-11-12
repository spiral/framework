<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Selective;

use Spiral\Attributes\ReaderInterface;
use Spiral\Attributes\ResolverInterface;

class SelectiveResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface[]
     */
    private $resolvers;

    /**
     * @param ResolverInterface[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritDoc}
     */
    public function isSupported(): bool
    {
        foreach ($this->resolvers as $resolver) {
            if (!$resolver->isSupported()) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function create(): ReaderInterface
    {
        $readers = [];

        foreach ($this->resolvers as $resolver) {
            $readers[] = $resolver->create();
        }

        return new SelectiveReader($readers);
    }
}

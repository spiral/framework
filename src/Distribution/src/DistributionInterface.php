<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Distribution;

/**
 * @template-implements \IteratorAggregate<string, ResolverInterface>
 */
interface DistributionInterface extends \IteratorAggregate, \Countable
{
    /**
     * @param string|null $name
     */
    public function resolver(string $name = null): UriResolverInterface;

    public function withDefault(string $name): self;
}

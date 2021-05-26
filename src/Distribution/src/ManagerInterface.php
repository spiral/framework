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
interface ManagerInterface extends \IteratorAggregate, \Countable
{
    /**
     * @param string|null $name
     * @return ResolverInterface
     */
    public function resolver(string $name = null): ResolverInterface;

    /**
     * @param string $name
     * @return $this
     */
    public function withDefault(string $name): self;
}

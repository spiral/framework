<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Distribution;

interface MutableManagerInterface extends ManagerInterface
{
    /**
     * @param string $name
     * @param ResolverInterface $resolver
     */
    public function add(string $name, ResolverInterface $resolver): void;
}

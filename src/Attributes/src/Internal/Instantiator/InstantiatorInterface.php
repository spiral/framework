<?php

/**
 * This file is part of Attributes package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

/**
 * @internal InstantiatorInterface is an internal library interface, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
interface InstantiatorInterface
{
    /**
     * @param \ReflectionClass $attr
     * @param array $arguments
     * @param string $context
     * @return object
     */
    public function instantiate(\ReflectionClass $attr, array $arguments, string $context): object;
}

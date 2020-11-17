<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Composite;

final class SelectiveReader extends Composite
{
    /**
     * {@inheritDoc}
     */
    protected function each(callable $resolver): iterable
    {
        foreach ($this->readers as $reader) {
            $result = $this->iterableToArray($resolver($reader));

            if (\count($result) > 0) {
                return $result;
            }
        }

        return [];
    }
}

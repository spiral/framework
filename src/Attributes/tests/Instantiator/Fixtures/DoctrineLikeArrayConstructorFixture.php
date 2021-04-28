<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Instantiator\Fixtures;

class DoctrineLikeArrayConstructorFixture
{
    public $a;
    public $b;
    public $c;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        foreach ($values as $name => $value) {
            if (\property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }
}

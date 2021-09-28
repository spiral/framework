<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Instantiator\Fixtures;

class VariadicConstructorFixture
{
    public $a;
    public $b;
    public $args;

    public function __construct($a = null, $b = null, ...$args)
    {
        $this->a = $a;
        $this->b = $b;
        $this->args = $args;
    }
}

<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Instantiator\Fixtures;

class NamedArgumentConstructorFixture
{
    public $a;
    public $b;
    public $c;

    public function __construct($a = null, $b = null, $c = null)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}

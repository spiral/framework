<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Instantiator;

use Spiral\Attributes\Internal\Instantiator\InstantiatorInterface;
use Spiral\Attributes\Internal\Instantiator\NamedArgumentsInstantiator;
use Spiral\Tests\Attributes\Instantiator\Fixtures\NamedArgumentConstructorFixture;

/**
 * @group unit
 * @group instantiator
 */
class NamedArgumentsInstantiatorTestCase extends InstantiatorTestCase
{
    /**
     * @return InstantiatorInterface
     */
    protected function getInstantiator(): InstantiatorInterface
    {
        return new NamedArgumentsInstantiator();
    }

    public function testNamedConstructorInstantiatable(): void
    {
        $object = $this->new(NamedArgumentConstructorFixture::class, [
            'a' => 23,
            'b' => 42,
        ]);

        $this->assertSame(23, $object->a);
        $this->assertSame(42, $object->b);
        $this->assertSame(null, $object->c);
    }
}

<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Instantiator;

use Spiral\Attributes\Internal\Instantiator\DoctrineInstantiator;
use Spiral\Attributes\Internal\Instantiator\InstantiatorInterface;
use Spiral\Tests\Attributes\Instantiator\Fixtures\DoctrineLikeNoConstructorFixture;
use Spiral\Tests\Attributes\Instantiator\Fixtures\DoctrineLikeArrayConstructorFixture;

/**
 * @group unit
 * @group instantiator
 */
class DoctrineInstantiatorTestCase extends InstantiatorTestCase
{
    /**
     * @return InstantiatorInterface
     */
    protected function getInstantiator(): InstantiatorInterface
    {
        return new DoctrineInstantiator();
    }

    public function testArrayConstructorInstantiatable(): void
    {
        /** @var DoctrineLikeArrayConstructorFixture $object */
        $object = $this->new(DoctrineLikeArrayConstructorFixture::class, [
            'a' => 23,
            'b' => 42,
        ]);

        $this->assertSame(23, $object->a);
        $this->assertSame(42, $object->b);
        $this->assertNull($object->c);
    }

    public function testNoConstructorInstantiatable(): void
    {
        /** @var DoctrineLikeNoConstructorFixture $object */
        $object = $this->new(DoctrineLikeNoConstructorFixture::class, [
            'a' => 23,
            'b' => 42,
        ]);

        $this->assertSame(23, $object->a);
        $this->assertSame(42, $object->b);
        $this->assertNull($object->c);
    }
}

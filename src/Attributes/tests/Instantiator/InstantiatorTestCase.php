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
use Spiral\Tests\Attributes\Concerns\InteractWithReflection;
use Spiral\Tests\Attributes\TestCase;

/**
 * @group unit
 * @group instantiator
 */
abstract class InstantiatorTestCase extends TestCase
{
    use InteractWithReflection;

    /**
     * @return InstantiatorInterface
     */
    abstract protected function getInstantiator(): InstantiatorInterface;

    /**
     * @template T of object
     * @param string $class
     * @param array $arguments
     * @return T
     * @throws \Throwable
     */
    protected function new(string $class, array $arguments = []): object
    {
        $reflection = $this->getReflectionClass($class);
        $instantiator = $this->getInstantiator();

        $object = $instantiator->instantiate($reflection, $arguments,
            $this->getReflectionClass(static::class)
        );

        self::assertInstanceOf($class, $object);

        return $object;
    }
}

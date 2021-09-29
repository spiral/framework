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
use Spiral\Tests\Attributes\Instantiator\Fixtures\NamedRequiredArgumentConstructorFixture;
use Spiral\Tests\Attributes\Instantiator\Fixtures\VariadicConstructorFixture;

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
        /** @var NamedArgumentConstructorFixture $object */
        $object = $this->new(NamedArgumentConstructorFixture::class, [
            'a' => 23,
            'b' => 42,
        ]);

        $this->assertSame(23, $object->a);
        $this->assertSame(42, $object->b);
        $this->assertSame(null, $object->c);
    }

    public function testMixedArgs(): void
    {
        /** @var NamedArgumentConstructorFixture $object */
        $object = $this->new(NamedArgumentConstructorFixture::class, [
            'A',
            'B',
            'c' => 'C',
        ]);

        $this->assertSame('A', $object->a);
        $this->assertSame('B', $object->b);
        $this->assertSame('C', $object->c);
    }

    public function testMessyIndices()
    {
        /** @var NamedArgumentConstructorFixture $object */
        $object = $this->new(NamedArgumentConstructorFixture::class, [
            1 => 'one',
            0 => 'zero',
        ]);

        $this->assertSame('one', $object->a);
        $this->assertSame('zero', $object->b);
        $this->assertSame(null, $object->c);
    }

    public function testUnknownSequentialAfterNamed()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->expectException(\BadMethodCallException::class);
        } else {
            $this->expectException(\Error::class);
        }
        /* @see NamedArgumentsInstantiator::ERROR_POSITIONAL_AFTER_NAMED */
        $this->expectExceptionMessageEquals('Cannot use positional argument after named argument');

        try {
            $this->new($class = NamedArgumentConstructorFixture::class, [
                'a' => 'A',
                5 => 'five',
            ]);
        } catch (\Throwable $e) {
            if (PHP_VERSION_ID < 80000) {
                self::assertExceptionSource($e, $class, 'function __construct');
            } else {
                self::assertExceptionSource($e, $class, 'class ');
            }
            throw $e;
        }
    }

    public function testKnownSequentialAfterNamed()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->expectException(\BadMethodCallException::class);
        } else {
            $this->expectException(\Error::class);
        }
        /* @see NamedArgumentsInstantiator::ERROR_POSITIONAL_AFTER_NAMED */
        $this->expectExceptionMessageEquals('Cannot use positional argument after named argument');

        $this->new(NamedArgumentConstructorFixture::class, [
            'a' => 'A',
            2 => 'five',
        ]);
    }

    public function testMissingArg()
    {
        $this->expectException(\ArgumentCountError::class);
        /* @see NamedArgumentsInstantiator::ERROR_ARGUMENT_NOT_PASSED */
        $this->expectExceptionMessageEquals(
            \sprintf(
                '%s::__construct(): Argument #2 ($b) not passed',
                NamedRequiredArgumentConstructorFixture::class
            )
        );

        try {
            $this->new($class = NamedRequiredArgumentConstructorFixture::class, [
                'a',
                'c' => 'C',
            ]);
        } catch (\Throwable $e) {
            if (PHP_VERSION_ID < 80000) {
                self::assertExceptionSource($e, $class, 'function __construct');
            } else {
                self::assertExceptionSource($e, $class, 'class ');
            }
            throw $e;
        }
    }

    public function testUnknownArg()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->expectException(\BadMethodCallException::class);
        } else {
            $this->expectException(\Error::class);
        }
        /* @see NamedArgumentsInstantiator::ERROR_UNKNOWN_ARGUMENT */
        $this->expectExceptionMessageEquals('Unknown named parameter $d');

        try {
            $this->new($class = NamedArgumentConstructorFixture::class, [
                'd' => 'D',
            ]);
        } catch (\Throwable $e) {
            if (PHP_VERSION_ID < 80000) {
                self::assertExceptionSource($e, $class, 'function __construct');
            } else {
                self::assertExceptionSource($e, $class, 'class ');
            }
            throw $e;
        }
    }

    public function testOverwriteArg()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->expectException(\BadMethodCallException::class);
        } else {
            $this->expectException(\Error::class);
        }
        /* @see NamedArgumentsInstantiator::ERROR_OVERWRITE_ARGUMENT */
        $this->expectExceptionMessageEquals('Named parameter $a overwrites previous argument');

        $this->new(NamedArgumentConstructorFixture::class, [
            'zero',
            'a' => 'A',
        ]);
    }

    public function testVariadicPositional()
    {
        /** @var VariadicConstructorFixture $object */
        $object = $this->new(VariadicConstructorFixture::class, [
            'A',
            'B',
            'C',
            'D',
        ]);

        $this->assertSame('A', $object->a);
        $this->assertSame('B', $object->b);
        $this->assertSame(['C', 'D'], $object->args);
    }

    public function testVariadicPositionalAfterNamed()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->expectException(\BadMethodCallException::class);
        } else {
            $this->expectException(\Error::class);
        }
        /* @see NamedArgumentsInstantiator::ERROR_POSITIONAL_AFTER_NAMED */
        $this->expectExceptionMessageEquals('Cannot use positional argument after named argument');

        $this->new(VariadicConstructorFixture::class, [
            'A',
            'b' => 'B',
            'c' => 'C',
            5 => 'five',
        ]);
    }

    public function testVariadicMixed()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->expectException(\BadMethodCallException::class);
            /* @see NamedArgumentsInstantiator::ERROR_NAMED_ARG_TO_VARIADIC */
            $this->expectExceptionMessageEquals('Cannot pass named argument $x to variadic parameter ...$args in PHP < 8');
        }

        /** @var VariadicConstructorFixture $object */
        $object = $this->new(VariadicConstructorFixture::class, [
            5 => 'five',
            4 => 'four',
            3 => 'three',
            'x' => 'X',
        ]);

        $this->assertSame('five', $object->a);
        $this->assertSame('four', $object->b);
        $this->assertSame(['three', 'x' => 'X'], $object->args);
    }

    public function testVariadicNamed()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->expectException(\BadMethodCallException::class);
            /* @see NamedArgumentsInstantiator::ERROR_NAMED_ARG_TO_VARIADIC */
            $this->expectExceptionMessageEquals('Cannot pass named argument $x to variadic parameter ...$args in PHP < 8');
        }

        /** @var VariadicConstructorFixture $object */
        $object = $this->new(VariadicConstructorFixture::class, [
            5 => 'five',
            'x' => 'X',
            'b' => 'B',
            'y' => 'Y',
        ]);

        $this->assertSame('five', $object->a);
        $this->assertSame('B', $object->b);
        $this->assertSame(['x' => 'X', 'y' => 'Y'], $object->args);
    }
}

<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader;

use Spiral\Attributes\Reader\NativeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tests\Attributes\Fixture\Native\NativeClass;
use Spiral\Tests\Attributes\Fixture\Native\NativeClassAnnotation;

/**
 * @requires PHP >= 8.0
 */
class NativeReaderTest extends ReaderImplementationTest
{
    protected function create(): ReaderInterface
    {
        return new NativeReader();
    }

    protected function getClassMetadata(): string
    {
        return NativeClassAnnotation::class;
    }

    protected function getImplementationClass(): string
    {
        return NativeClass::class;
    }
}

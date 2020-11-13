<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader;

use Spiral\Attributes\Reader\DoctrineReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tests\Attributes\Fixture\Doctrine\DoctrineClass;
use Spiral\Tests\Attributes\Fixture\Doctrine\DoctrineClassAnnotation;

class DoctrineReaderTest extends ReaderImplementationTest
{
    protected function create(): ReaderInterface
    {
        return new DoctrineReader();
    }

    protected function getClassMetadata(): string
    {
        return DoctrineClassAnnotation::class;
    }

    protected function getImplementationClass(): string
    {
        return DoctrineClass::class;
    }
}

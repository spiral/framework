<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader;

use Spiral\Attributes\AnnotationReader;
use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\Composite\MergeReader;
use Spiral\Attributes\ReaderInterface;

/**
 * Count of annotations should be x2 of original values, except non-supported
 * by Doctrine reader ({@see DoctrineReaderTest} for more information).
 */
class MergeReaderTest extends ReaderTestCase
{
    protected $classMetadataCount = 2;
    protected $methodMetadataCount = 2;
    protected $propertyMetadataCount = 2;

    protected function getReader(): ReaderInterface
    {
        return new MergeReader([
            new AttributeReader(),
            new AnnotationReader(),
        ]);
    }
}

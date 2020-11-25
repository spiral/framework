<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Attributes\Reader;

use Spiral\Attributes\Internal\NativeAttributeReader;
use Spiral\Attributes\ReaderInterface;

/**
 * @requires PHP >= 8.0
 */
class NativeReaderTest extends ReaderTestCase
{
    protected function getReader(): ReaderInterface
    {
        return new NativeAttributeReader();
    }
}

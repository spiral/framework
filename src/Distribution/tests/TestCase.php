<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Tests\Distribution;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\UriInterface;

/**
 * @group unit
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param string $uri
     * @return UriInterface
     */
    protected function uri(string $uri): UriInterface
    {
        return new Uri($uri);
    }
}

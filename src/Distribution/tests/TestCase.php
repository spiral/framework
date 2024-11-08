<?php

declare(strict_types=1);

namespace Spiral\Tests\Distribution;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\UriInterface;

#[\PHPUnit\Framework\Attributes\Group('unit')]
abstract class TestCase extends BaseTestCase
{
    protected function uri(string $uri): UriInterface
    {
        return new Uri($uri);
    }
}

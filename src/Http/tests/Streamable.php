<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use Psr\Http\Message\StreamInterface;
use Spiral\Streams\StreamableInterface;

class Streamable implements StreamableInterface
{
    public function __construct(private StreamInterface $stream)
    {
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use Psr\Http\Message\StreamInterface;
use Spiral\Streams\StreamableInterface;

class Streamable implements StreamableInterface
{
    private $stream;

    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }
}

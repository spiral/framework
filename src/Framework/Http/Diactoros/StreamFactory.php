<?php

declare(strict_types=1);

namespace Spiral\Http\Diactoros;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Laminas\Diactoros\Stream;

final class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = \fopen('php://temp', 'r+');
        \fwrite($resource, $content);
        \rewind($resource);
        return $this->createStreamFromResource($resource);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = \fopen($filename, $mode);
        return $this->createStreamFromResource($resource);
    }

    public function createStreamFromResource(mixed $resource): StreamInterface
    {
        return new Stream($resource);
    }
}

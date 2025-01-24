<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Diactoros;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Nyholm\Psr7\Stream;

final class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = \fopen('php://temp', 'r+');
        \fwrite($resource, $content);
        \rewind($resource);
        return $this->createStreamFromResource($resource);
    }

    public function createStreamFromFile(string $file, string $mode = 'r'): StreamInterface
    {
        $resource = \fopen($file, $mode);
        return $this->createStreamFromResource($resource);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return Stream::create($resource);
    }
}

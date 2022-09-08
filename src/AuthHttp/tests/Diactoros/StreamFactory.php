<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Diactoros;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Nyholm\Psr7\Stream;

final class StreamFactory implements StreamFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'rb+');
        fwrite($resource, $content);
        rewind($resource);
        return $this->createStreamFromResource($resource);
    }

    /**
     * @inheritdoc
     */
    public function createStreamFromFile(string $file, string $mode = 'rb'): StreamInterface
    {
        $resource = fopen($file, $mode);
        return $this->createStreamFromResource($resource);
    }

    /**
     * @inheritdoc
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return Stream::create($resource);
    }
}

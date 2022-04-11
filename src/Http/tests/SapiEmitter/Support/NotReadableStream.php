<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\SapiEmitter\Support;

use Psr\Http\Message\StreamInterface;

/**
 * @source https://github.com/yiisoft/yii-web/blob/master/tests/Emitter/Support/NotReadableStream.php
 * @license MIT
 * @copyright Yii Software LLC (http://www.yiisoft.com) All rights reserved.
 */
class NotReadableStream implements StreamInterface
{
    public function __toString(): string
    {
        throw new \RuntimeException();
    }

    public function close(): void
    {
    }

    public function detach()
    {
        return null;
    }

    public function getSize(): ?int
    {
        return null;
    }

    public function tell(): int
    {
        throw new \RuntimeException();
    }

    public function eof(): bool
    {
        return false;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new \RuntimeException();
    }

    public function rewind(): void
    {
        throw new \RuntimeException();
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new \RuntimeException();
    }

    public function isReadable(): bool
    {
        return false;
    }

    public function read($length): string
    {
        throw new \RuntimeException();
    }

    public function getContents(): string
    {
        throw new \RuntimeException();
    }

    public function getMetadata($key = null)
    {
        return null;
    }
}

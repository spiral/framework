<?php

declare(strict_types=1);

namespace Spiral\Http\Stream;

use Generator;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

final class GeneratorStream implements StreamInterface
{
    private ?Generator $stream;

    private bool $readable = true;

    private ?int $size = null;

    private int $caret = 0;

    private bool $started = false;

    public function __construct(Generator $body)
    {
        $this->stream = $body;
    }

    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (\Exception) {
            return '';
        }
    }

    public function close(): void
    {
        if ($this->stream !== null) {
            $this->detach();
        }
    }

    public function detach()
    {
        if ($this->stream === null) {
            return null;
        }
        $this->stream = null;
        $this->size = null;
        $this->caret = 0;
        $this->started = false;
        $this->readable = false;
        return null;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function tell(): int
    {
        return $this->caret;
    }

    public function eof(): bool
    {
        return $this->stream === null || !$this->stream->valid();
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek($offset, $whence = \SEEK_SET): void
    {
        throw new RuntimeException('Stream is not seekable.');
    }

    public function rewind(): void
    {
        if ($this->stream !== null) {
            $this->stream->rewind();
        }
        $this->caret = 0;
        $this->started = false;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new RuntimeException('Cannot write to a non-writable stream.');
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read($length): string
    {
        if (!$this->readable) {
            throw new RuntimeException('Cannot read from non-readable stream.');
        }
        if ($this->stream === null) {
            throw new RuntimeException('Cannot read from detached stream.');
        }
        do {
            if ($this->started) {
                $read = (string)$this->stream->send(null);
            } else {
                $this->started = true;
                $read = (string)$this->stream->current();
            }
            if (!$this->stream->valid()) {
                $read .= $this->stream->getReturn();
                break;
            }
        } while ($read === '');
        $this->caret += \strlen($read);
        if (!$this->stream->valid()) {
            $this->size = $this->caret;
        }
        return $read;
    }

    public function getContents(): string
    {
        if ($this->stream === null) {
            throw new RuntimeException('Unable to read stream contents.');
        }
        $content = '';
        do {
            $content .= $this->read(PHP_INT_MAX);
        } while ($this->stream->valid());
        return $content;
    }

    public function getMetadata($key = null)
    {
        if ($this->stream === null) {
            return $key ? null : [];
        }

        $meta = [
            'seekable' => $this->isSeekable(),
            'eof' => $this->eof(),
        ];

        if (null === $key) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }
}

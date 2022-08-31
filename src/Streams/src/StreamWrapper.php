<?php

declare(strict_types=1);

namespace Spiral\Streams;

use Psr\Http\Message\StreamInterface;
use Spiral\Streams\Exception\WrapperException;

/**
 * Spiral converter of PSR-7 streams to virtual filenames. Static as hell.
 */
final class StreamWrapper
{
    /**
     * Stream context.
     *
     * @var resource
     */
    public mixed $context = null;

    private static bool $registered = false;

    /**
     * Uris associated with StreamInterfaces.
     */
    private static array $uris = [];

    private static array $modes = [
        'r'   => 33060,
        'rb'  => 33060,
        'r+'  => 33206,
        'rb+' => 33206,
        'w'   => 33188,
        'wb'  => 33188,
    ];

    private ?StreamInterface $stream = null;
    private string $mode = '';

    /**
     * Check if StreamInterface ended.
     */
    public function stream_eof(): bool
    {
        if ($this->stream === null) {
            throw new WrapperException('Stream is not opened.');
        }

        return $this->stream->eof();
    }

    /**
     * Open pre-mocked StreamInterface by it's unique uri.
     */
    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        if (!isset(self::$uris[$path])) {
            return false;
        }

        $this->stream = self::$uris[$path];
        $this->mode = $mode;

        $this->stream->rewind();

        return true;
    }

    /**
     * Read data from StreamInterface.
     */
    public function stream_read(int $length): string|false
    {
        if ($this->stream === null) {
            throw new WrapperException('Stream is not opened.');
        }

        return $this->stream->isReadable() ? $this->stream->read($length) : false;
    }

    /**
     * Seek into StreamInterface.
     */
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        if ($this->stream === null) {
            throw new WrapperException('Stream is not opened.');
        }

        //Note, MongoDB native streams DO NOT support seeking at the moment
        //@see https://jira.mongodb.org/browse/PHPLIB-213
        $this->stream->seek($offset, $whence);

        return true;
    }

    /**
     * Get StreamInterface stats.
     *
     * @see stat()
     */
    public function stream_stat(): array
    {
        if ($this->stream === null) {
            throw new WrapperException('Stream is not opened.');
        }

        return $this->getStreamStats($this->stream);
    }

    /**
     * Get StreamInterface position.
     */
    public function stream_tell(): int
    {
        if ($this->stream === null) {
            throw new WrapperException('Stream is not opened.');
        }

        //Note, MongoDB native streams DO NOT support seeking at the moment
        //@see https://jira.mongodb.org/browse/PHPLIB-213
        return $this->stream->tell();
    }

    /**
     * Write content into StreamInterface.
     */
    public function stream_write(string $data): int
    {
        if ($this->stream === null) {
            throw new WrapperException('Stream is not opened.');
        }

        return $this->stream->write($data);
    }

    /**
     * Get stats based on wrapped StreamInterface by it's mocked uri.
     *
     * @see stat()
     */
    public function url_stat(string $path, int $flag): array|false
    {
        try {
            if (isset(self::$uris[$path])) {
                return $this->getStreamStats(self::$uris[$path]);
            }
        } catch (\Throwable $e) {
            if (($flag & \STREAM_URL_STAT_QUIET) === \STREAM_URL_STAT_QUIET) {
                return false;
            }
            \trigger_error($e->getMessage(), \E_USER_ERROR);
        }

        return false;
    }

    /**
     * Register stream wrapper.
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        \stream_wrapper_register('spiral', self::class);

        self::$registered = true;
    }

    /**
     * Check if given uri or stream has been allocated.
     */
    public static function has(StreamInterface|string $file): bool
    {
        if ($file instanceof StreamInterface) {
            $file = 'spiral://' . \spl_object_hash($file);
        }

        return isset(self::$uris[$file]);
    }

    /**
     * Create StreamInterface associated resource.
     *
     * @return resource
     * @throws WrapperException
     */
    public static function getResource(StreamInterface $stream)
    {
        $mode = null;
        if ($stream->isReadable()) {
            $mode = 'r';
        }

        if ($stream->isWritable()) {
            $mode = !empty($mode) ? 'r+' : 'w';
        }

        if (empty($mode)) {
            throw new WrapperException('Stream is not available in read or write modes');
        }

        return \fopen(self::getFilename($stream), $mode);
    }

    /**
     * Register StreamInterface and get unique url for it.
     */
    public static function getFilename(StreamInterface $stream): string
    {
        self::register();

        $uri = 'spiral://' . \spl_object_hash($stream);
        self::$uris[$uri] = $stream;

        return $uri;
    }

    /**
     * Free uri dedicated to specified StreamInterface. Method is useful for long living
     * applications. You must close resource by yourself!
     *
     * @param string|StreamInterface $file String uri or StreamInterface.
     */
    public static function release(StreamInterface|string $file): void
    {
        if ($file instanceof StreamInterface) {
            $file = 'spiral://' . \spl_object_hash($file);
        }

        unset(self::$uris[$file]);
    }

    /**
     * Helper method used to correctly resolve StreamInterface stats.
     */
    private function getStreamStats(StreamInterface $stream): array
    {
        $mode = $this->mode;
        if (empty($mode)) {
            if ($stream->isReadable()) {
                $mode = 'r';
            }

            if ($stream->isWritable()) {
                $mode = !empty($mode) ? 'r+' : 'w';
            }
        }

        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => self::$modes[$mode],
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => (string)$stream->getSize(),
            'atime'   => 0,
            'mtime'   => 0,
            'ctime'   => 0,
            'blksize' => 0,
            'blocks'  => 0,
        ];
    }
}

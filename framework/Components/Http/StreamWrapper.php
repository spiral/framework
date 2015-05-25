<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\StreamInterface;

class StreamWrapper
{
    /**
     * Indication that wrapper was already registered.
     *
     * @var bool
     */
    private static $registered = false;

    /**
     * Uris associated with StreamInterfaces.
     *
     * @var array
     */
    private static $uris = array();

    /**
     * Stream context.
     *
     * @var resource
     */
    public $context = null;

    /**
     * Associated stream.
     *
     * @var StreamInterface
     */
    private $stream = null;


    /**
     * Close StreamInterface.
     */
    public function stream_close()
    {
        //TODO: check if this is nesessary
    }

    /**
     * Check if StreamInterface ended.
     *
     * @return bool
     */
    public function stream_eof()
    {
        return $this->stream->eof();
    }

    /**
     * Open pre-mocked StreamInterface by it's unique uri.
     *
     * @param string $path
     * @param string $mode
     * @param int    $options
     * @param string &$opened_path
     * @return bool
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        return fopen(__FILE__, 'rb');
    }

    /**
     * Read data from StreamInterface.
     *
     * @param int $count
     * @return string
     */
    public function stream_read($count)
    {
        return $this->stream->read($count);
    }

    /**
     * Seek into StreamInterface.
     *
     * @param int $offset
     * @param int $whence = SEEK_SET
     * @return bool
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return $this->stream->seek($offset, $whence);
    }

    /**
     * Get StreamInterface stats.
     *
     * @see stat()
     * @return array|null
     */
    public function stream_stat()
    {
        return $this->getStreamStats($this->stream);
    }

    /**
     * Get StreamInterface position.
     *
     * @return int
     */
    public function stream_tell()
    {
        return $this->stream->tell();
    }

    /**
     * Write content into StreamInterface.
     *
     * @param string $data
     * @return int
     */
    public function stream_write($data)
    {
        return $this->stream->write($data);
    }

    /**
     * Get stats based on wrapped StreamInterface by it's mocked uri.
     *
     * @see stat()
     * @param string $path
     * @param int    $flags
     * @return array|null
     */
    public function url_stat($path, $flags)
    {
        if (!isset(self::$uris[$path]))
        {
            return null;
        }

        return $this->getStreamStats(self::$uris[$path]);
    }

    private function getStreamStats(StreamInterface $stream)
    {
        return array(
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 33060, //TODO: right mode
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => (string)$stream->getSize(),
            'atime'   => 0,
            'mtime'   => 0,
            'ctime'   => 0,
            'blksize' => 0,
            'blocks'  => 0
        );
    }

    /**
     * Register stream wrapper.
     */
    public static function register()
    {
        if (self::$registered)
        {
            return;
        }

        stream_wrapper_register('spiral', __CLASS__);
        self::$registered = true;
    }

    /**
     * Register StreamInterface and get unique url for it.
     *
     * @param StreamInterface $stream
     * @return string
     */
    public static function getUri(StreamInterface $stream)
    {
        self::register();

        $uri = 'spiral://' . spl_object_hash($stream);
        self::$uris[$uri] = $stream;

        return $uri;
    }

    public static function getResource(StreamInterface $stream)
    {
    }

    /**
     * Free uri dedicated to specified StreamInterface. Method is useful for long living applications.
     *
     * @param string|StreamInterface $uri String uri or StreamInterface.
     */
    public static function releaseUri($uri)
    {
        if ($uri instanceof StreamInterface)
        {
            $uri = 'spiral://' . spl_object_hash($uri);
        }

        unset(self::$uris[$uri]);
    }
}
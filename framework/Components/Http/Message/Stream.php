<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Message;

use Psr\Http\Message\StreamableInterface;

class Stream implements StreamableInterface
{
    /**
     * Associated stream resource.
     *
     * @var resource
     */
    protected $resource = null;

    /**
     * Stream meta information.
     *
     * @see stream_get_meta_data()
     * @var array
     */
    protected $metadata = array();

    /**
     * Create new Stream instance based on provided stream resource or uri (including filenames).
     *
     * @param string|resource $stream Stream resource or URI.
     * @param string          $mode   Read/write mode.
     */
    public function __construct($stream = 'php://memory', $mode = 'r')
    {
        $this->resource = $stream;
        if (is_string($stream))
        {
            $this->resource = fopen($stream, $mode);
        }

        if (!is_resource($this->resource))
        {
            throw new \InvalidArgumentException("Unable to open provided stream resource.");
        }

        $this->metadata = stream_get_meta_data($this->resource);
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if (empty($this->resource))
        {
            return;
        }

        fclose($this->detach());
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any.
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        $this->metadata = array();

        return $resource;
    }

    /**
     * Get the size of the stream if known
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        return null;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int|bool Position of the file pointer or false on error.
     */
    public function tell()
    {
        if (empty($this->resource))
        {
            return false;
        }

        return ftell($this->resource);
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return !empty($this->resource) && feof($this->resource);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return !empty($this->resource) && $this->metadata['seekable'];
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated based on the seek offset.
     *                    Valid values are identical to the built-in PHP $whence values for `fseek()`.
     *                    SEEK_SET: Set position equal to offset bytes
     *                    SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return $this->isSeekable() && fseek($this->resource, $offset, $whence) === 0;
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will return FALSE, indicating failure; otherwise,
     * it will perform a seek(0), and return the status of that operation.
     *
     * @see  seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function rewind()
    {
        return $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        if (empty($this->resource))
        {
            return false;
        }

        return strstr($this->metadata['mode'], 'w') || strstr($this->metadata['mode'], '+');
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int|bool Returns the number of bytes written to the stream on success or FALSE on failure.
     */
    public function write($string)
    {
        if ($this->isWritable())
        {
            return fwrite($this->resource, $string);
        }

        return false;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        if (empty($this->resource))
        {
            return false;
        }

        return strstr($this->metadata['mode'], 'r') || strstr($this->metadata['mode'], '+');
    }

    /**
     * Read data from the stream.
     *
     * @param int $length   Read up to $length bytes from the object and return them. Fewer than $length
     *                      bytes may be returned if underlying stream call returns fewer bytes.
     * @return string|false Returns the data read from the stream, false if unable to read or if an
     *                      error occurs.
     */
    public function read($length)
    {
        if (!$this->isReadable())
        {
            return false;
        }

        return $this->eof() ? '' : fread($this->resource, $length);
    }

    /**
     * Returns the remaining contents in a string
     *
     * @param bool $rewind Rewind stream position (if possible).
     * @return string
     */
    public function getContents($rewind = false)
    {
        $rewind && $this->rewind();

        return $this->isReadable() ? stream_get_contents($this->resource) : '';
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key       Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is provided. Returns a specific
     *                          key value if a key is provided and the value is found, or null if the
     *                          key is not found.
     */
    public function getMetadata($key = null)
    {
        if (empty($key))
        {
            return $this->metadata;
        }

        return array_key_exists($key, $this->metadata) ? $this->metadata[$key] : null;
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before reading data and read
     * the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->isReadable() ? stream_get_contents($this->resource, -1, 0) : '';
    }
}
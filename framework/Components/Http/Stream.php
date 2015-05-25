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

/**
 * Describes a data stream.
 *
 * Typically, an instance will wrap a PHP stream; this interface provides
 * a wrapper around the most common operations, including serialization of
 * the entire stream to a string.
 */
class Stream implements StreamInterface
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
            throw new \InvalidArgumentException("Unable to open provided stream resource/path.");
        }

        $this->metadata = stream_get_meta_data($this->resource);
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        if (!$this->isReadable())
        {
            return '';
        }

        try
        {
            return stream_get_contents($this->resource, -1, 0);
        }
        catch (\RuntimeException $exception)
        {
            return '';
        }
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
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        $this->metadata = array();

        return $resource;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        if (empty($this->resource))
        {
            return null;
        }

        return fstat($this->resource)['size'];
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        if (empty($this->resource))
        {
            throw new \RuntimeException("Unable to tell steam position in detached state.");
        }

        $position = ftell($this->resource);

        if (!is_int($position))
        {
            throw new \RuntimeException("Unable to retrieve stream position.");
        }

        return $position;
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
     * @param int $whence Specifies how the cursor position will be calculated
     *                    based on the seek offset. Valid values are identical to the built-in
     *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                    offset bytes SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (empty($this->resource))
        {
            throw new \RuntimeException("Unable to seek steam in detached state.");
        }

        if (!$this->isSeekable())
        {
            throw new \RuntimeException("Unable to seek steam, stream is not seekable.");
        }

        if (fseek($this->resource, $offset, $whence) !== 0)
        {
            throw new \RuntimeException("Unable to seek stream.");
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see  seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        $this->seek(0);
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

        return is_writable($this->metadata['uri']);
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
        if ($this->isWritable())
        {
            throw new \RuntimeException("Unable to write into stream, stream is not writable.");
        }

        if (($result = fwrite($this->resource, $string)) === false)
        {
            throw new \RuntimeException('Unable to write into stream, an error occurred.');
        }

        return $result;
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
     * @param int $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *                    if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        if (!$this->isReadable())
        {
            throw new \RuntimeException(
                "Unable to read from stream, stream is not readable or in detached state."
            );
        }

        if (($result = fread($this->resource, $length)) === false)
        {
            throw new \RuntimeException('Unable to read from stream, an error occurred.');
        }

        return $result;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        if (!$this->isReadable())
        {
            return '';
        }

        if (($result = stream_get_contents($this->resource)) === false)
        {
            throw new \RuntimeException('Unable to read from stream, an error occurred.');
        }

        return $result;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *                    provided. Returns a specific key value if a key is provided and the
     *                    value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if (empty($key))
        {
            return $this->metadata;
        }

        return array_key_exists($key, $this->metadata) ? $this->metadata[$key] : null;
    }
}
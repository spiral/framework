<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Request;

use Spiral\Components\Http\Message\Stream;

class InputStream extends Stream
{
    /**
     * Cached stream content, required to correctly resolve behaviour when php://input can't be read
     * twice.
     *
     * @invisible
     * @var string
     */
    protected $cached = '';

    /**
     * Indication that steam ended and can't be re-read (cache will be used).
     *
     * @invisible
     * @var bool
     */
    protected $ended = false;

    /**
     * Create new Stream instance based on provided stream resource or uri (including filenames). Php
     * input has to be cached as it can be read only once.
     *
     * @link https://github.com/phly/http/blob/master/src/PhpInputStream.php
     * @link http://php.net/manual/en/wrappers.php.php
     * @param string|resource $stream Stream resource or URI.
     * @param string          $mode   Read/write mode.
     */
    public function __construct($stream = 'php://input', $mode = 'r')
    {
        parent::__construct($stream, 'r');
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
        $resource = parent::detach();
        $this->cached = '';
        $this->ended = false;

        return $resource;
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return true;
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
        if (($content = parent::read($length)) && !$this->ended)
        {
            $this->cached .= $content;
        }

        $this->ended = $this->ended || $this->eof();

        return $content;
    }

    /**
     * Returns the remaining contents in a string.
     *
     * @param int $maxLength
     * @return string
     */
    public function getContents($maxLength = -1)
    {
        if ($this->ended)
        {
            return $this->cached;
        }

        $contents = stream_get_contents($this->resource, $maxLength);
        $this->cached .= $contents;

        if ($maxLength === -1 || $this->eof())
        {
            $this->ended = true;
        }

        return $contents;
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
        if ($key)
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
        if (!$this->ended)
        {
            //Reading the rest
            $this->getContents();
        }

        return $this->cached;
    }
}
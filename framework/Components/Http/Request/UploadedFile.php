<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Request;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Components\Http\HttpDispatcher;
use Spiral\Components\Http\Message\Stream;
use Spiral\Core\Core;
use Symfony\Component\Process\Exception\RuntimeException;

class UploadedFile implements UploadedFileInterface
{
    /**
     * Block size used to moved file to final destination.
     */
    const STREAM_BLOCK_SIZE = HttpDispatcher::STREAM_BLOCK_SIZE;

    /**
     * Temporary file path.
     *
     * @var string
     */
    private $path = '';

    /**
     * Uploaded file error.
     *
     * @var int
     */
    private $error = 0;

    /**
     * Uploaded file size.
     *
     * @var int
     */
    private $size = 0;

    /**
     * Client filename (not safe).
     *
     * @var string
     */
    private $clientFilename = '';

    /**
     * Client media type, not safe.
     *
     * @var string
     */
    private $clientMediaType = '';

    /**
     * Pre-constructed file stream.
     *
     * @var null|StreamInterface
     */
    private $stream = null;

    /**
     * File becomes unavailable after moving.
     *
     * @var bool
     */
    private $isUnavailable = false;

    /**
     * New instance of Uploaded file.
     *
     * @param string|Stream|resource $path            Local filename, or stream, or resource.
     * @param int                    $size            Uploaded filesize.
     * @param int                    $error           Upload error.
     * @param null                   $clientFilename  Non safe client filename.
     * @param null                   $clientMediaType Non safe client mediatype.
     */
    public function __construct($path, $size, $error, $clientFilename = null, $clientMediaType = null)
    {
        if (is_string($path))
        {
            $this->file = $path;
        }
        elseif (is_resource($path))
        {
            $this->stream = new Stream($path);
        }
        elseif ($path instanceof StreamInterface)
        {
            $this->stream = $path;
        }

        $this->size = $size;
        $this->error = $error;

        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        if ($this->isUnavailable)
        {
            throw new \RuntimeException("File was moved and not available anymore.");
        }

        if (!empty($this->stream))
        {
            return $this->stream;
        }

        return $this->stream = new Stream($this->path);
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \InvalidArgumentException if the $path specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *                           the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if ($this->isUnavailable)
        {
            throw new \RuntimeException("File was moved and not available anymore.");
        }

        if (Core::isConsole() || !PHP_SAPI || empty($this->path))
        {
            $stream = $this->getStream();

            if (!$destination = fopen($targetPath, 'wb+'))
            {
                throw new RuntimeException("An error occurred while moving uploaded file.");
            }

            $stream->rewind();
            while (!$stream->eof())
            {
                fwrite($destination, $stream->read(self::STREAM_BLOCK_SIZE));
            }

            fclose($destination);

            //No more need in our stream
            $stream->close();
            $this->stream = null;
        }

        if (move_uploaded_file($this->file, $targetPath) === false)
        {
            throw new RuntimeException("An error occurred while moving uploaded file.");
        }

        $this->isUnavailable = true;
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}
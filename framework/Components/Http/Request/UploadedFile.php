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
use Spiral\Components\Http\Stream;
use Spiral\Core\Core;
use Symfony\Component\Process\Exception\RuntimeException;

class UploadedFile implements UploadedFileInterface
{
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
     * @invisible
     * @var null|StreamInterface
     */
    private $stream = null;

    /**
     * File becomes unavailable after moving.
     *
     * @var bool
     */
    private $isMoved = false;

    /**
     * New instance of Uploaded file.
     *
     * @param string|StreamInterface|resource $path            Local filename, or stream, or resource.
     * @param int                             $size            Uploaded filesize.
     * @param int                             $error           Upload error.
     * @param null                            $clientFilename  Non safe client filename.
     * @param null                            $clientMediaType Non safe client mediatype.
     */
    public function __construct($path, $size, $error, $clientFilename = null, $clientMediaType = null)
    {
        if (is_string($path))
        {
            $this->path = $path;
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

        if (empty($clientFilename) && !empty($path))
        {
            $clientFilename = basename($path);
        }

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
        if ($this->isMoved)
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
        if ($this->isMoved)
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
                fwrite($destination, $stream->read(Stream::READ_BLOCK_SIZE));
            }

            fclose($destination);

            //No more need in our stream
            $stream->close();
            $this->stream = null;
        }

        if (move_uploaded_file($this->path, $targetPath) === false)
        {
            throw new RuntimeException("An error occurred while moving uploaded file.");
        }

        $this->isMoved = true;
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

    /**
     * Convert global $_FILES to normalized tree when every file represented by UploadedFileInterface.
     *
     * @param array $files Usually global $_FILES.
     * @return array|UploadedFileInterface[]
     * @throws \InvalidArgumentException
     */
    public static function normalizeFiles($files)
    {
        $result = array();

        foreach ($files as $key => $value)
        {
            if ($value instanceof UploadedFileInterface)
            {
                $result[$key] = $value;
                continue;
            }

            if (!is_array($value))
            {
                throw new \InvalidArgumentException("Invalid uploaded files tree structure.");
                continue;
            }

            if (is_array($value) && isset($value['tmp_name']))
            {
                $result[$key] = self::createUploadedFile($value);
                continue;
            }

            if (is_array($value))
            {
                $result[$key] = self::normalizeFiles($value);
                continue;
            }
        }

        return $result;
    }

    /**
     * Create UploadedFileInterface instance based on provided tree structure.
     *
     * @param array $file $_FILES item compatible structure.
     * @return array|UploadedFileInterface
     */
    private static function createUploadedFile(array $file)
    {
        if (is_array($file['tmp_name']))
        {
            return self::normalizeNestedFiles($file);
        }

        return new UploadedFile(
            $file['tmp_name'],
            $file['size'],
            $file['error'],
            $file['name'],
            $file['type']
        );
    }

    /**
     * Perform normalization to resolve files uploaded under nested name (array of files).
     *
     * @param array $files
     * @return UploadedFileInterface[]
     */
    private static function normalizeNestedFiles(array $files)
    {
        $result = array();
        foreach (array_keys($files['tmp_name']) as $key)
        {
            $result[$key] = self::createUploadedFile(array(
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key]
            ));
        }

        return $result;
    }
}
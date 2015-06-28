<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Validation\Checkers;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Files\StreamWrapper;
use Spiral\Components\Storage\StorageObject;
use Spiral\Support\Validation\Checker;

class FileChecker extends Checker
{
    /**
     * Set of default error messages associated with their check methods organized by method name.
     * Will be returned by the checker to replace the default validator message. Can have placeholders
     * for interpolation.
     *
     * @var array
     */
    protected $messages = [
        "exists"    => "[[There was an error while uploading '{field}' file.]]",
        "size"      => "[[File '{field}' exceeds the maximum file size of {1}KB.]]",
        "extension" => "[[File '{field}' has an invalid file format.]]"
    ];

    /**
     * FileManager component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * New instance for file checker. File checker depends on the File component.
     *
     * @param FileManager $file
     */
    public function __construct(FileManager $file)
    {
        $this->file = $file;
    }

    /**
     * Helper function that retrieves the real filename. Method can accept as real filename or instance
     * of UploadedFileInterface. Use second parameter to pass only uploaded files.
     *
     * @param string|UploadedFileInterface $file         Local filename or uploaded file array.
     * @param bool                         $onlyUploaded Pass only uploaded files.
     * @return string|bool
     */
    protected function getFilename($file, $onlyUploaded = true)
    {
        if ($file instanceof UploadedFileInterface | $file instanceof StorageObject)
        {
            if ($file->getError())
            {
                return false;
            }

            return StreamWrapper::getUri($file->getStream());
        }

        if ($onlyUploaded && !$this->file->isUploaded($file, true))
        {
            return false;
        }

        return $this->file->exists($file) ? $file : false;
    }

    /**
     * Will check if the local file exists.
     *
     * @param array|string $file Local file or uploaded file array.
     * @return bool
     */
    public function exists($file)
    {
        return (bool)$this->getFilename($file, false);
    }

    /**
     * Will check if local file exists or just uploaded.
     *
     * @param array|string $file Local file or uploaded file array.
     * @return bool
     */
    public function uploaded($file)
    {
        if ($file instanceof UploadedFileInterface)
        {
            return !$file->getError();
        }

        return (bool)$this->getFilename($file, true);
    }

    /**
     * Checks to see if the filesize is smaller than the minimum size required. The size is set in
     * KBytes.
     *
     * @param array|string $file Local file or uploaded file array.
     * @param int          $size Max filesize in kBytes.
     * @return bool
     */
    public function size($file, $size)
    {
        if (!$file = $this->getFilename($file, false))
        {
            return false;
        }

        return $this->file->size($file) < $size * 1024;
    }

    /**
     * Pass files where public or local name has allowed extensions. This is soft validation, no
     * real guarantee that extension was not manually modified by client.
     *
     * @param array|string $file       Local file or uploaded file array.
     * @param array|mixed  $extensions Array of acceptable extensions.
     * @return bool
     */
    public function extension($file, $extensions)
    {
        if ($file instanceof UploadedFileInterface)
        {
            if (!is_array($extensions))
            {
                $extensions = array_slice(func_get_args(), 1);
            }

            return in_array($this->file->extension($file->getClientFilename()), $extensions);
        }

        return in_array($this->file->extension($file), $extensions);
    }
}
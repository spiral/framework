<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Validation\Checkers;

use Spiral\Components\Files\FileManager;
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
    protected $messages = array(
        "exists"    => "[[There was an error while uploading '{field}' file.]]",
        "size"      => "[[File '{field}' exceeds the maximum file size of {1}KB.]]",
        "extension" => "[[File '{field}' has an invalid file format.]]"
    );

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
     * Helper function that retrieves the real filename. Can accept both local filename or an uploaded
     * file array. To validate the file array as a local file (without checking for is_uploaded_file()),
     * array must have the field "local" filled in. This trick can be used with some more complex
     * validators or file processors.
     *
     * @param string|array $file         Local filename or uploaded file array.
     * @param bool         $onlyUploaded Pass only uploaded files.
     * @return string
     */
    protected function getFilename($file, $onlyUploaded = true)
    {
        if ($onlyUploaded && !$this->file->isUploaded($file, true))
        {
            return false;
        }

        if (is_array($file) && array_key_exists('tmp_name', $file))
        {
            return $this->file->exists($file['tmp_name']) ? $file['tmp_name'] : false;
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
     * Pass files where public or local name has allowed extensions.
     *
     * @param array|string $file       Local file or uploaded file array.
     * @param array|mixed  $extensions Array of acceptable extensions.
     * @return bool
     */
    public function extension($file, $extensions)
    {
        if (is_array($file))
        {
            if (!is_array($extensions))
            {
                $extensions = array_slice(func_get_args(), 1);
            }

            return in_array($this->file->extension($file['name']), $extensions);
        }

        return in_array($this->file->extension($file), $extensions);
    }
}
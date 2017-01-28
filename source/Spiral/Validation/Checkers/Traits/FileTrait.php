<?php
/**
 * Spiral Framework, Core Components
 *
 * @author    Wolfy-J
 */

namespace Spiral\Validation\Checkers\Traits;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Files\FilesInterface;
use Spiral\Files\Streams\StreamableInterface;
use Spiral\Files\Streams\StreamWrapper;

/**
 * Provides ability to get filename for uploaded file.
 */
trait FileTrait
{
    /**
     * @var FilesInterface
     */
    protected $files;

    /**
     * Internal method to fetch filename using multiple input formats.
     *
     * @param mixed|UploadedFileInterface|StreamableInterface $file
     *
     * @return string|null
     */
    private function resolveFilename($file)
    {
        if (empty($file)) {
            return null;
        }

        if (
            ($file instanceof UploadedFileInterface && $file->getError() === 0)
            || $file instanceof StreamableInterface
        ) {
            return StreamWrapper::localFilename($file->getStream());
        }

        if (is_array($file)) {
            //Temp filename.
            $file = $file['tmp_name'];
        }

        if (!is_string($file) || !$this->files->exists($file)) {
            return null;
        }

        return $file;
    }

    /**
     * Check if file being uploaded.
     *
     * @param mixed|UploadedFileInterface $file Filename or file array.
     *
     * @return bool
     */
    private function isUploaded($file): bool
    {
        if (is_string($file)) {
            //We can use native method
            return is_uploaded_file($file);
        }

        if (is_array($file)) {
            return isset($file['tmp_name']) && is_uploaded_file($file['tmp_name']);
        }

        if ($file instanceof UploadedFileInterface) {
            return empty($file->getError());
        }

        //Not uploaded
        return false;
    }
}
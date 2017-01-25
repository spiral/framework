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
     * @param mixed|UploadedFileInterface $filename
     * @param bool                        $onlyUploaded Check if file uploaded.
     *
     * @return string|bool
     */
    protected function filename($filename, bool $onlyUploaded = true)
    {
        if (empty($filename) || ($onlyUploaded && !$this->isUploaded($filename))) {
            return false;
        }

        if (
            $filename instanceof UploadedFileInterface || $filename instanceof StreamableInterface
        ) {
            return StreamWrapper::localFilename($filename->getStream());
        }

        if (is_array($filename)) {
            $filename = $filename['tmp_name'];
        }

        return $this->files->exists($filename) ? $filename : false;
    }

    /**
     * Check if file being uploaded.
     *
     * @param mixed|UploadedFileInterface $filename Filename or file array.
     *
     * @return bool
     */
    private function isUploaded($filename): bool
    {
        if (is_string($filename)) {
            //We can use native method
            return is_uploaded_file($filename);
        }

        if (is_array($filename)) {
            return isset($filename['tmp_name']) && is_uploaded_file($filename['tmp_name']);
        }

        if ($filename instanceof UploadedFileInterface) {
            return empty($filename->getError());
        }

        return false;
    }
}
<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation\Checker\Traits;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Files\FilesInterface;
use Spiral\Streams\StreamableInterface;
use Spiral\Streams\StreamWrapper;

/**
 * Provides ability to get filename for uploaded file.
 */
trait FileTrait
{
    /** @var FilesInterface */
    protected $files;

    /**
     * Internal method to fetch filename using multiple input formats.
     *
     * @param mixed|UploadedFileInterface|StreamableInterface $file
     * @return string|null
     */
    private function resolveFilename($file): ?string
    {
        if (empty($file)) {
            return null;
        }

        if (
            $file instanceof StreamableInterface ||
            ($file instanceof UploadedFileInterface && $file->getError() === 0)
        ) {
            return StreamWrapper::getFilename($file->getStream());
        }

        if (is_array($file)) {
            if (!isset($file['tmp_name'])) {
                return null;
            }

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
     * @return bool
     */
    private function isUploaded($file): bool
    {
        if (is_string($file)) {
            //We can use native method
            return is_uploaded_file($file);
        }

        if (is_array($file)) {
            return isset($file['tmp_name']) && (
                is_uploaded_file($file['tmp_name']) || isset($file['uploaded'])
            );
        }

        if ($file instanceof UploadedFileInterface) {
            return empty($file->getError());
        }

        //Not uploaded
        return false;
    }
}

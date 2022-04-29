<?php

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
    protected FilesInterface $files;

    /**
     * Internal method to fetch filename using multiple input formats.
     */
    private function resolveFilename(mixed $file): ?string
    {
        return match (true) {
            empty($file) => null,
            $this->isStreamableFile($file) => StreamWrapper::getFilename($file->getStream()),
            \is_array($file) => !isset($file['tmp_name']) ? null : $file['tmp_name'],
            !\is_string($file) || !$this->files->exists($file) => null,
            default => $file
        };
    }

    private function isStreamableFile(mixed $file): bool
    {
        return
            $file instanceof StreamableInterface ||
            ($file instanceof UploadedFileInterface && $file->getError() === 0);
    }

    /**
     * Check if file being uploaded.
     */
    private function isUploaded(mixed $file): bool
    {
        $isUploadedArray = static fn (array $file) =>
            isset($file['tmp_name']) &&
            (\is_uploaded_file($file['tmp_name']) || isset($file['uploaded']));

        return match (true) {
            \is_string($file) => \is_uploaded_file($file),
            $file instanceof UploadedFileInterface => empty($file->getError()),
            \is_array($file) => $isUploadedArray($file),
            // Not uploaded
            default => false
        };
    }
}

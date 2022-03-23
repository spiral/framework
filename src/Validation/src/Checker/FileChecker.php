<?php

declare(strict_types=1);

namespace Spiral\Validation\Checker;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Files\FilesInterface;
use Spiral\Validation\AbstractChecker;
use Spiral\Validation\Checker\Traits\FileTrait;

/**
 * @inherit-messages
 */
final class FileChecker extends AbstractChecker implements SingletonInterface
{
    use FileTrait;

    public const MESSAGES = [
        'exists'    => '[[File does not exists.]]',
        'uploaded'  => '[[File not received, please try again.]]',
        'size'      => '[[File exceeds the maximum file size of {1}KB.]]',
        'extension' => '[[File has an invalid file format.]]',
    ];

    public const ALLOW_EMPTY_VALUES = ['exists', 'uploaded'];

    public function __construct(FilesInterface $files)
    {
        $this->files = $files;
    }

    /**
     * Check if file exist.
     */
    public function exists(mixed $file): bool
    {
        return !empty($this->resolveFilename($file));
    }

    /**
     * Check if file been uploaded.
     *
     * @param mixed $file Local file or uploaded file array.
     */
    public function uploaded(mixed $file): bool
    {
        return $this->isUploaded($file);
    }

    /**
     * Check if file size less that specified value in KB.
     *
     * @param mixed $file Local file or uploaded file array.
     * @param int   $size Size in KBytes.
     */
    public function size(mixed $file, int $size): bool
    {
        if (empty($filename = $this->resolveFilename($file))) {
            return false;
        }

        return $this->files->size($filename) <= ($size * 1024);
    }

    /**
     * Check if file extension in whitelist. Client name of uploaded file will be used!
     * It is recommended to use external validation like media type based on file mimetype or
     * ensure that resource is properly converted.
     */
    public function extension(UploadedFileInterface|string $file, array|string $extensions): bool
    {
        if (!\is_array($extensions)) {
            $extensions = \array_slice(\func_get_args(), 1);
        }

        if ($file instanceof UploadedFileInterface) {
            return \in_array(
                $this->files->extension($file->getClientFilename()),
                $extensions,
                true
            );
        }

        return \in_array($this->files->extension($file), $extensions, true);
    }
}

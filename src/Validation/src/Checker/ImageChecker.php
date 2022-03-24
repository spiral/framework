<?php

declare(strict_types=1);

namespace Spiral\Validation\Checker;

use Psr\Http\Message\UploadedFileInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Files\FilesInterface;
use Spiral\Streams\StreamableInterface;
use Spiral\Validation\AbstractChecker;
use Spiral\Validation\Checker\Traits\FileTrait;

/**
 * @inherit-messages
 */
final class ImageChecker extends AbstractChecker implements SingletonInterface
{
    use FileTrait;

    /**
     * Getimagesize constants.
     */
    public const WIDTH      = 0;
    public const HEIGHT     = 1;
    public const IMAGE_TYPE = 2;

    public const MESSAGES = [
        'type'    => '[[Image format not supported.]]',
        'valid'   => '[[Image format not supported (allowed JPEG, PNG or GIF).]]',
        'smaller' => '[[Image size should not exceed {1}x{2}px.]]',
        'bigger'  => '[[The image dimensions should be at least {1}x{2}px.]]',
    ];

    public const ALLOW_EMPTY_VALUES = ['type', 'valid'];

    /**
     * Known image types.
     *
     * @var array
     */
    public const IMAGE_TYPES = [
        'null',
        'gif',
        'jpeg',
        'png',
        'swf',
        'psd',
        'bmp',
        'tiff',
        'tiff',
        'jpc',
        'jp2',
        'jpx',
        'jb2',
        'swc',
        'iff',
        'wbmp',
        'xbm',
    ];

    public function __construct(FilesInterface $files)
    {
        $this->files = $files;
    }

    /**
     * Check if image in a list of allowed image types.
     */
    public function type(mixed $file, array|string $types): bool
    {
        $image = $this->imageData($file);
        if ($image === false) {
            return false;
        }

        if (!\is_array($types)) {
            $types = \array_slice(\func_get_args(), 1);
        }

        if (!isset(self::IMAGE_TYPES[$image[self::IMAGE_TYPE]])) {
            return false;
        }

        return \in_array(self::IMAGE_TYPES[$image[self::IMAGE_TYPE]], $types, true);
    }

    /**
     * Shortcut to check if image has valid type (JPEG, PNG and GIF are allowed).
     */
    public function valid(mixed $file): bool
    {
        return $this->type($file, ['jpeg', 'png', 'gif']);
    }

    /**
     * Check if image smaller that specified rectangle (height check if optional).
     *
     * @param int $height Optional.
     */
    public function smaller(StreamableInterface|string|UploadedFileInterface $file, int $width, int $height): bool
    {
        if (empty($image = $this->imageData($file))) {
            return false;
        }

        return $image[self::WIDTH] <= $width
            && $image[self::HEIGHT] <= $height;
    }

    /**
     * Check if image is bigger that specified rectangle (height check is optional).
     *
     * @param int|null $height Optional.
     */
    public function bigger(StreamableInterface|string|UploadedFileInterface $file, int $width, int $height = null): bool
    {
        if (empty($image = $this->imageData($file))) {
            return false;
        }

        return $image[self::WIDTH] >= $width && $image[self::HEIGHT] >= $height;
    }

    /**
     * Internal method, return image details fetched by getimagesize() or false.
     *
     * @see getimagesize()
     */
    protected function imageData(mixed $file): array|bool
    {
        $filename = $this->resolveFilename($file);
        if (empty($filename)) {
            return false;
        }

        return @getimagesize($filename);
    }
}

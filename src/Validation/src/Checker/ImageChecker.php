<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

    /**
     * {@inheritdoc}
     */
    public const MESSAGES = [
        'type'    => '[[Image format not supported.]]',
        'valid'   => '[[Image format not supported (allowed JPEG, PNG or GIF).]]',
        'smaller' => '[[Image size should not exceed {1}x{2}px.]]',
        'bigger'  => '[[The image dimensions should be at least {1}x{2}px.]]',
    ];

    /**
     * {@inheritdoc}
     */
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

    /**
     * @param FilesInterface $files
     */
    public function __construct(FilesInterface $files)
    {
        $this->files = $files;
    }

    /**
     * Check if image in a list of allowed image types.
     *
     * @param string|UploadedFileInterface|StreamableInterface $file
     * @param array|string                                     $types
     * @return bool
     */
    public function type($file, $types): bool
    {
        $image = $this->imageData($file);
        if ($image === false) {
            return false;
        }

        if (!is_array($types)) {
            $types = array_slice(func_get_args(), 1);
        }

        if (!isset(self::IMAGE_TYPES[$image[self::IMAGE_TYPE]])) {
            return false;
        }

        return in_array(self::IMAGE_TYPES[$image[self::IMAGE_TYPE]], $types, true);
    }

    /**
     * Shortcut to check if image has valid type (JPEG, PNG and GIF are allowed).
     *
     * @param string|UploadedFileInterface|StreamableInterface $file
     * @return bool
     */
    public function valid($file): bool
    {
        return $this->type($file, ['jpeg', 'png', 'gif']);
    }

    /**
     * Check if image smaller that specified rectangle (height check if optional).
     *
     * @param string|UploadedFileInterface|StreamableInterface $file
     * @param int                                              $width
     * @param int                                              $height Optional.
     * @return bool
     */
    public function smaller($file, int $width, int $height): bool
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
     * @param string|UploadedFileInterface|StreamableInterface $file
     * @param int                                              $width
     * @param int|null                                         $height Optional.
     * @return bool
     */
    public function bigger($file, int $width, int $height = null): bool
    {
        if (empty($image = $this->imageData($file))) {
            return false;
        }

        return $image[self::WIDTH] >= $width && $image[self::HEIGHT] >= $height;
    }

    /**
     * Internal method, return image details fetched by getimagesize() or false.
     *
     * @param string|mixed $file
     * @return array|bool
     *
     * @see getimagesize()
     */
    protected function imageData($file)
    {
        $filename = $this->resolveFilename($file);
        if (empty($filename)) {
            return false;
        }

        return @getimagesize($filename);
    }
}

<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Validation\Checkers;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Files\FilesInterface;
use Spiral\Files\Streams\StreamableInterface;
use Spiral\Validation\Checkers\Traits\FileTrait;
use Spiral\Validation\Prototypes\AbstractChecker;

/**
 * Image based validations.
 */
class ImageChecker extends AbstractChecker implements SingletonInterface
{
    use FileTrait;

    /**
     * Getimagesize constants.
     */
    const WIDTH      = 0;
    const HEIGHT     = 1;
    const IMAGE_TYPE = 2;

    /**
     * {@inheritdoc}
     */
    const MESSAGES = [
        'type'    => '[[Image does not supported.]]',
        'valid'   => '[[Image does not supported (allowed JPEG, PNG or GIF).]]',
        'smaller' => '[[Image size should not exceed {0}x{1}px.]]',
        'bigger'  => '[[The image dimensions should be at least {0}x{1}px.]]',
    ];

    /**
     * Known image types.
     *
     * @var array
     */
    const IMAGE_TYPES = [
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
     * @param FilesInterface     $files
     * @param ContainerInterface $container
     */
    public function __construct(FilesInterface $files, ContainerInterface $container = null)
    {
        $this->files = $files;

        parent::__construct($container);
    }

    /**
     * Check if image in a list of allowed image types.
     *
     * @param string|UploadedFileInterface|StreamableInterface $file
     * @param array|string                                     $types
     *
     * @return bool
     */
    public function type($file, $types): bool
    {
        if (empty($image = $this->imageData($file))) {
            return false;
        }

        if (!is_array($types)) {
            $types = array_slice(func_get_args(), 1);
        }

        return in_array(
            self::IMAGE_TYPES[$image[self::IMAGE_TYPE]],
            $types
        );
    }

    /**
     * Shortcut to check if image has valid type (JPEG, PNG and GIF are allowed).
     *
     * @param string|UploadedFileInterface|StreamableInterface $file
     *
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
     *
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
     * @param int                                              $height Optional.
     *
     * @return bool
     */
    public function bigger($file, int $width, int $height = null): bool
    {
        if (empty($image = $this->imageData($file))) {
            return false;
        }

        return $image[self::WIDTH] >= $width
            && $image[self::HEIGHT] >= $height;
    }

    /**
     * Internal method, return image details fetched by getimagesize() or false.
     *
     * @see getimagesize()
     *
     * @param string|mixed $file
     *
     * @return array|bool
     */
    protected function imageData($file)
    {
        try {
            return getimagesize($this->resolveFilename($file));
        } catch (\Exception $e) {
            //We can simply invalidate image if system can't read it
        }

        return false;
    }
}

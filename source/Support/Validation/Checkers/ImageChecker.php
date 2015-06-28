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

class ImageChecker extends FileChecker
{
    /**
     * Getimagesize constants.
     */
    const WIDTH      = 0;
    const HEIGHT     = 1;
    const IMAGE_TYPE = 2;

    /**
     * Set of default error messages associated with their check methods organized by method name.
     * Will be returned by the checker to replace the default validator message. Can have placeholders
     * for interpolation.
     *
     * @var array
     */
    protected $messages = [
        "type"    => "[[The file '{field}' is not a supported image type.]]",
        "valid"   => "[[The file '{field}' should be a valid image (JPEG, PNG or GIF).]]",
        "smaller" => "[[The image dimensions of '{field}' should not exceed {0}x{1}px.]]",
        "bigger"  => "[[The image dimensions of '{field}' should be at least {0}x{1}px.]]"
    ];

    /**
     * All known image types. This type will be associated with value from getimagesize() and can be
     * retrieved via ImageObject->type()
     *
     * @var array
     */
    protected $imageTypes = [
        'null', 'gif', 'jpeg', 'png', 'swf', 'psd', 'bmp', 'tiff',
        'tiff', 'jpc', 'jp2', 'jpx', 'jb2', 'swc', 'iff', 'wbmp', 'xbm'
    ];

    /**
     * New instance of image checker. Image checker depends on the Image and File components.
     *
     * @param FileManager $file
     */
    public function __construct(FileManager $file)
    {
        $this->file = $file;
    }

    /**
     * Helper function to fetch image information from specified file or stream.
     *
     * @param string|array $file Local filename or file array.
     * @return array
     */
    protected function getImage($file)
    {
        $filename = $this->getFilename($file);

        try
        {
            return getimagesize($filename);
        }
        catch (\Exception $exception)
        {
        }

        return false;
    }

    /**
     * Check image type by parsing it's header. This image type can be different than file extension
     * and driven by image component. GD library is required.
     *
     * @param array|string $file  Local file or uploaded file array.
     * @param array|mixed  $types Image types (can be different than file extension).
     * @return bool
     */
    public function type($file, $types)
    {
        if (!$image = $this->getImage($file))
        {
            return false;
        }

        if (!is_array($types))
        {
            $types = array_slice(func_get_args(), 1);
        }

        return in_array($this->imageTypes[$image[self::IMAGE_TYPE]], $types);
    }

    /**
     * Check if the uploaded file is a valid image (alias for type with jpeg, png and gif types).
     *
     * @param array|string $file Local file or uploaded file array.
     * @return bool
     */
    public function valid($file)
    {
        return $this->type($file, ['jpeg', 'png', 'gif']);
    }

    /**
     * Check if image fits in the maximum acceptable dimensions.
     *
     * @param string $file   Local file or uploaded file array.
     * @param int    $width  Max image width.
     * @param int    $height Max image height (not required by default).
     * @return bool
     */
    public function smaller($file, $width, $height = null)
    {
        if (!$image = $this->getImage($file))
        {
            return false;
        }

        if ($image[self::WIDTH] >= $width)
        {
            return false;
        }

        if ($height && $image[self::HEIGHT] >= $height)
        {
            return false;
        }

        return true;
    }

    /**
     * Check if image fits in minimum acceptable dimensions.
     *
     * @param string $file   Local file or uploaded file array.
     * @param int    $width  Min image width.
     * @param int    $height Min image height (not required by default).
     * @return bool
     */
    public function bigger($file, $width, $height = null)
    {
        if (!$image = $this->getImage($file))
        {
            return false;
        }

        if ($image[self::WIDTH] < $width)
        {
            return false;
        }

        if ($height && $image[self::HEIGHT] < $height)
        {
            return false;
        }

        return true;
    }
}
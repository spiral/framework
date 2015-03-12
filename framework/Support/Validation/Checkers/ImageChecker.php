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
use Spiral\Components\Image\ImageManager;
use Spiral\Components\Image\ImageObject;

class ImageChecker extends FileChecker
{
    /**
     * Set of default error messages associated with their check methods organized by method name.
     * Will be returned by the checker to replace the default validator message. Can have placeholders
     * for interpolation.
     *
     * @var array
     */
    protected $messages = array(
        "type"    => "[[The file '{field}' is not a supported image type.]]",
        "valid"   => "[[The file '{field}' should be a valid image (JPEG, PNG or GIF).]]",
        "smaller" => "[[The image dimensions of '{field}' should not exceed {0}x{1}px.]]",
        "bigger"  => "[[The image dimensions of '{field}' should be at least {0}x{1}px.]]"
    );

    /**
     * Image component.
     *
     * @var ImageManager
     */
    protected $image = null;

    /**
     * New instance of image checker. Image checker depends on the Image and File components.
     *
     * @param FileManager  $file
     * @param ImageManager $image
     */
    public function __construct(FileManager $file, ImageManager $image)
    {
        $this->image = $image;
        $this->file = $file;
    }

    /**
     * Previously opened ImageObjects. This is used to speed up the script while applying multiple
     * rules to one image.
     *
     * @var ImageObject[]
     */
    static protected $imageObjects = array();

    /**
     * Helper function to get ImageObject from a non specified input. Can accept both local filename
     * or uploaded file array. To validate the file array as a local file (without checking for
     * is_uploaded_file()), array must have the field "local" filled in. This trick can be used with
     * some of the more complex validators or file processors.
     *
     * @param string|array $file Local filename or file array.
     * @return ImageObject|bool
     */
    protected function getImage($file)
    {
        $filename = $this->getFilename($file);
        if (isset(self::$imageObjects[$filename]))
        {
            return self::$imageObjects[$filename];
        }

        if (!$this->file->exists($filename))
        {
            return false;
        }

        $image = $this->image->open($filename);
        if (!$image->isSupported())
        {
            return false;
        }

        return self::$imageObjects[$filename] = $image;
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

        return in_array($image->type(), $types);
    }

    /**
     * Check if the uploaded file is a valid image (alias for type with jpeg, png and gif types).
     *
     * @param array|string $file Local file or uploaded file array.
     * @return bool
     */
    public function valid($file)
    {
        return $this->type($file, array('jpeg', 'png', 'gif'));
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

        if ($image->width() >= $width)
        {
            return false;
        }

        if ($height && $image->height() >= $height)
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

        if ($image->width() <= $width)
        {
            return false;
        }

        if ($height && $image->height() <= $height)
        {
            return false;
        }

        return true;
    }

    /**
     * Will erase all ImageObjects created for validation from memory.
     */
    public static function cleanCache()
    {
        self::$imageObjects = array();
    }
}
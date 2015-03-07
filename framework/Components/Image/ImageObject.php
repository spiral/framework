<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Image;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;

/**
 * @method static ImageObject make(array $parameters = array());
 *
 * @property string   $filename
 * @property int      $type
 * @property int      $width
 * @property int      $height
 * @property string   $mimetype
 * @property array    $properties
 * @property string   $orientation
 * @property IptcData $iptc
 */
class ImageObject extends Component
{
    /**
     * Orientation constants. Will be returned via ImageObject->orientation() method.
     */
    const PORTRAIT  = 'portrait';
    const SQUARE    = 'square';
    const LANDSCAPE = 'landscape';
    const PANORAMIC = 'panoramic';

    /**
     * Line types used for drawing commands. Used in ImageObject->drawLine(), drawRectangle() and other methods.
     */
    const LINE_SOLID  = 0;
    const LINE_DOTTED = 1;

    /**
     * Colorspaces.
     */
    const RGB        = 'RGB';
    const GRAY_SCALE = 'grayScale';

    /**
     * All known image types. This type will be associated with value from getimagesize() and can be retrieved via
     * ImageObject->type()
     *
     * @var array
     */
    static public $imageTypes = array(
        'null', 'gif', 'jpeg', 'png', 'swf', 'psd', 'bmp', 'tiff',
        'tiff', 'jpc', 'jp2', 'jpx', 'jb2', 'swc', 'iff', 'wbmp', 'xbm'
    );

    /**
     * Filename used to create ImageObject, image processor will be associated withimage processor.
     *
     * @var string
     */
    protected $filename = '';

    /**
     * Properties retrieved from image file using getimagesize() function.
     *
     * Index 0 and 1 contains respectively the width and the height of the image.
     *
     * Some formats may contain no image or may contain multiple images. In these cases, getimagesize() might not be able
     * to properly determine the image size. getimagesize() will return zero for width and height in these cases.
     *
     * Index 2 is one of the IMAGETYPE_XXX constants indicating the type of the image.
     *
     * Index 3 is a text string with the correct height="yyy" width="xxx" string that can be used directly in an IMG tag.
     *
     * Index 4 - mime is the correspondant MIME type of the image. This information can be used to deliver images with
     * correct the HTTP Content-type header.
     *
     * Index 5 - channels will be 3 for RGB pictures and 4 for CMYK pictures (bits is the number of bits for each color).
     * For some image types, the presence of channels and bits values can be a bit confusing. As an example, GIF always
     * uses 3 channels per pixel, but the number of bits per pixel cannot be calculated for an animated GIF with a global
     * color table.
     *
     * @var array
     */
    protected $properties = array();

    /**
     * IPTC metadata embedded in images are often referred to as "IPTC headers", and can be easily encoded and decoded by
     * most popular photo editing software. Usually contains software identified, picture title, keywords and etc.
     *
     * @var IptcData
     */
    protected $iptc = null;

    /**
     * Image processor represents operations associated with one specific image file, all processing operation (resize, crop
     * and etc) described via operations sequence and perform on image save, every ImageObject will have it's own processor.
     *
     * Every processor will implement set of pre-defined operations, however additional operations can be supported by processor
     * and extend default set of image manipulations.
     *
     * Use ImageObject->processor() to get currently associated image processor, or named method to force processor selection.
     * Changing processor while using ImageObject will erase all unsaved (not performed) commands.
     *
     * @var ProcessorInterface
     */
    protected $processor = null;

    /**
     * Open existed filename and create ImageObject based on it, ImageObject->isSupported() method can be used to verify
     * that file is supported and can be processed. ImageObject preferred to be used for processing existed images, rather
     * that creating new.
     *
     * @param string $filename Local image filename.
     * @throws ImageException
     */
    public function __construct($filename)
    {
        if (!function_exists('getimagesize'))
        {
            throw new ImageException("Unable to find required function 'getimagesize', GD2 extension required.");
        }

        if (!FileManager::getInstance()->exists($filename))
        {
            return;
        }

        try
        {
            $this->properties = getimagesize($filename, $imageinfo);
            $this->iptc = IptcData::make(compact('filename', 'imageinfo'));
            $this->filename = $filename;

            if (!array_key_exists($this->properties[2], static::$imageTypes))
            {
                $this->properties = array();
                $this->filename = false;
            }
        }
        catch (\Exception $exception)
        {
        }
    }

    /**
     * True if image was opened successfully and image type supported by processors. Currently will return true if file is
     * valid image from perspective of getimagesize() function, without checking for processors support (assuming every
     * processor support known image types).
     *
     * @return bool
     */
    public function isSupported()
    {
        return (bool)$this->filename;
    }

    /**
     * Local image path associated with ImageObject, empty if image not supported.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * String representation of image type (jpeg for jpg images), empty if image not supported.
     *
     * @return string
     */
    public function type()
    {
        return $this->isSupported() ? static::$imageTypes[$this->properties[2]] : '';
    }

    /**
     * Image width in pixels, 0 if not supported.
     *
     * @return int
     */
    public function width()
    {
        return $this->isSupported() ? $this->properties[0] : 0;
    }

    /**
     * Image height in pixels, 0 if not supported.
     *
     * @return int
     */
    public function height()
    {
        return $this->isSupported() ? $this->properties[1] : 0;
    }

    /**
     * Image mimetype (can be used for Content-Type header), empty if image not supported.
     *
     * @return string
     */
    public function mimetype()
    {
        return $this->isSupported() ? $this->properties['mime'] : '';
    }

    /**
     * Properties retrieved from image file using getimagesize() function.
     *
     * Index 0 and 1 contains respectively the width and the height of the image.
     *
     * Some formats may contain no image or may contain multiple images. In these cases, getimagesize() might not be able
     * to properly determine the image size. getimagesize() will return zero for width and height in these cases.
     *
     * Index 2 is one of the IMAGETYPE_XXX constants indicating the type of the image.
     *
     * Index 3 is a text string with the correct height="yyy" width="xxx" string that can be used directly in an IMG tag.
     *
     * Index 4 - mime is the correspondant MIME type of the image. This information can be used to deliver images with
     * correct the HTTP Content-type header.
     *
     * Index 5 - channels will be 3 for RGB pictures and 4 for CMYK pictures (bits is the number of bits for each color).
     * For some image types, the presence of channels and bits values can be a bit confusing. As an example, GIF always
     * uses 3 channels per pixel, but the number of bits per pixel cannot be calculated for an animated GIF with a global
     * color table.
     *
     * @return array
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * Detected image orientation (landscape, portrait, panoramic). Result can be represented as string or compared using
     * Image orientation constants. This method will detect square images with definer error* value and panoramic images
     * where aspect ratio is higher that specified value.
     *
     * @param float $squareError    Error in ratio (around 1) to be treated as square.
     * @param float $panoramicRatio Image with ratio higher than specified value (2 by default) will be treated as panoramic.
     * @return bool|string
     */
    public function orientation($squareError = 0.05, $panoramicRatio = 2.0)
    {
        if (!$this->isSupported())
        {
            return false;
        }

        $width = $this->width();
        $height = $this->height();

        if ($width / $height >= $panoramicRatio)
        {
            //Width more than height in 2 or more times
            return self::PANORAMIC;
        }
        elseif (abs($width - $height) / max($width, $height) <= $squareError)
        {
            //Image is square, or near square
            return self::SQUARE;
        }
        else
        {
            return $width >= $height ? self::LANDSCAPE : self::PORTRAIT;
        }
    }

    /**
     * IPTC metadata embedded in images are often referred to as "IPTC headers", and can be easily encoded and decoded by
     * most popular photo editing software. Usually contains software identified, picture title, keywords and etc.
     * For more complex image metadata manipulations consider using: http://www.sno.phy.queensu.ca/~phil/exiftool/
     *
     * @return IptcData
     */
    public function iptc()
    {
        return $this->isSupported() ? $this->iptc : null;
    }

    /**
     * Fetch image dominant colors using ImageColors class, colors will be based on image pixelization representation with
     * specified mixing mode. This is slow function, do not use it in non background operations. Colors will be returned
     * as assosicated array where color is key and percent is value, colors will be sorted from most common to least common
     * color.
     *
     * Use with small images!
     *
     * @param int   $countColors Maximum amount of colors to be fetched.
     * @param int   $dimension   Amount of tiles image should be chunked to, more tiles - more colors, more time.
     * @param float $step        Smaller step - more accurate colors will be detected, but more time will be required.
     * @param int   $mixMode     Mix mode specifies how to detect dominant color in tile, adaptive will select most notable
     *                           color.
     * @return array
     */
    public function getColors($countColors = 10, $dimension = 15, $step = 0.005, $mixMode = ImageAnalyzer::mixAdaptive)
    {
        if (!$this->isSupported())
        {
            return array();
        }

        $analysis = ImageAnalyzer::make(array('filename' => $this->filename) + compact('dimension', 'mixMode'));
        $analysis->analyzeImage();

        return $analysis->fetchColors($countColors, $step);
    }

    /**
     * Method to accept image functions view magic property.
     *
     * @param string $method One of pre-defined methods.
     * @return mixed
     */
    public function __get($method)
    {
        return $this->$method();
    }

    /**
     * Command to perform proportional image resize.
     *
     * @param int $width  Maximum image width.
     * @param int $height Maximum image height.
     * @return static
     */
    public function resize($width, $height)
    {
        $this->processor()->resize($width, $height);

        $ratio = $this->width / $this->height;
        if ($ratio <= ($width / $height))
        {
            $this->properties[0] = round($height * $ratio);
            $this->properties[1] = round($height);
        }
        else
        {
            $this->properties[0] = round($width);
            $this->properties[1] = round($width / $ratio);
        }

        $this->properties[3] = "width=\"{$this->width}\" height=\"{$this->height}\"";

        return $this;
    }

    /**
     * Command to crop image area defined by coordinates and dimensions.
     *
     * @param int $x      Crop area left top X coordinate.
     * @param int $y      Crop area left top Y coordinate.
     * @param int $width  Crop area width dimension.
     * @param int $height Crop area height dimension.
     * @return static
     */
    public function crop($x, $y, $width, $height)
    {
        $this->processor()->crop($x, $y, $width, $height);

        $this->properties[0] = min($this->width - $x, $width);
        $this->properties[1] = min($this->height - $y, $height);
        $this->properties[3] = "width=\"{$this->width}\" height=\"{$this->height}\"";

        return $this;
    }

    /**
     * Combine two images together, ImageObject provided in first argument has to be supported and will be placed at top
     * of currently open image, position, width and heights can be specified in arguments.
     *
     * @param ImageObject $image   Overlay image.
     * @param int         $opacity Opacity (0 - overlay absolutely transparent, 100 - watermark is not transparent).
     * @param int         $x       Composited image X coordinate.
     * @param int         $y       Composited image Y coordinate.
     * @param int|bool    $width   Composited image width. Original overlay image width by default.
     * @param int|bool    $height  Composited image height. Original overlay image height by default.
     * @return static
     */
    public function composite(ImageObject $image, $opacity = 100, $x = 0, $y = 0, $width = false, $height = false)
    {
        $this->processor()->composite(
            realpath($image->filename),
            $x,
            $y,
            $width === false ? $image->width : $width,
            $height === false ? $image->height : $height,
            $opacity
        );

        return $this;
    }

    /**
     * Draw line with specified color, width, coordinates and style.
     *
     * @param int    $x1    Start X position.
     * @param int    $y1    Start Y position.
     * @param int    $x2    End X position.
     * @param int    $y2    End Y position.
     * @param string $color Line color in RGB format.
     * @param int    $width Line width in pixels.
     * @param int    $style Line style, solid and dotted styles supported by default.
     * @return static
     */
    public function line($x1, $y1, $x2, $y2, $color, $width = 1, $style = self::LINE_SOLID)
    {
        $this->processor()->line($x1, $y1, $x2, $y2, $color, $width, $style);

        return $this;
    }

    /**
     * Draw rectangle width specified stroke style, stoke color, background fill colors and coordinates.
     *
     * @param int    $x1        Left top X coordinate.
     * @param int    $y1        Left top Y coordinate.
     * @param int    $x2        Bottom right X coordinate.
     * @param int    $y2        Bottom right Y coordinate.
     * @param string $fillColor Rectangle fill color in RGB format.
     * @param string $color     Stroke color in RGB format.
     * @param int    $width     Stroke width in pixels.
     * @param int    $style     Stroke style, solid and dotted styles supported by default.
     * @return static
     */
    public function rectangle($x1, $y1, $x2, $y2, $fillColor, $color, $width = 1, $style = self::LINE_SOLID)
    {
        $this->processor()->rectangle($x1, $y1, $x2, $y2, $fillColor, $color, $width, $style);

        return $this;
    }

    /**
     * Draw text annotation with specified location, color and font.
     *
     * @param int    $x      Annotation left bottom X coordinate.
     * @param int    $y      Annotation left bottom Y coordinate.
     * @param string $string Text should be drawed.
     * @param string $color  Annotation color.
     * @param string $font   Font filename or known font identifier.
     * @param int    $size   Text size in pixels (can very for different fonts).
     * @param int    $angle  Text angle.
     * @return static
     */
    public function annotation($x, $y, $string, $color, $font = '', $size = 12, $angle = 0)
    {
        $this->processor()->annotation($x, $y, $string, $color, $font, $size, $angle);

        return $this;
    }

    /**
     * Blurring images so they become fuzzy may not seem like a useful operation, but actually is very useful for generating
     * background effects and shadows. It is also very useful for smoothing the effects of the 'jaggies' to anti-alias
     * the edges of images, and to round out features to produce highlighting effects.
     *
     * The important setting in the above is the sigma value. It can be thought of as an approximation of just how much
     * your want the image to 'spread' or blur, in pixels. Think of it as the size of the brush used to blur the image.
     * The numbers are floating point values, so you can use a very small value like '0.5'.
     *
     * @param float $sigma  It can be thought of as an approximation of just how much your want the image to 'spread' or
     *                      blur, in pixels.
     * @param int   $radius Blur radius (0 to detect automatically).
     * @return static
     */
    public function blur($sigma, $radius = 0)
    {
        $this->processor()->blur($sigma, $radius);

        return $this;
    }

    /**
     * Sharpens an image. We convolve the image with a Gaussian operator of the given radius and standard deviation (sigma).
     * Radius not specified, so processor have to detect it automatically.
     *
     * @param float $sigma  The standard deviation of the Gaussian, in pixels.
     * @param int   $radius The radius of the Gaussian, in pixels, not counting the center pixel. Use 0 for auto-select.
     * @return static
     */
    public function sharpen($sigma, $radius = 0)
    {
        $this->processor()->sharpen($sigma, $radius);

        return $this;
    }

    /**
     * Convert image colorspace to gray scale, will convert all existed colors to black and white
     * representation.
     *
     * @return static
     */
    public function grayscale()
    {
        $this->processor()->grayscale();

        return $this;
    }

    /**
     * Crop part of image defined by dimensions, instead of providing crop area coordinates as in crop() method, cup will
     * accept string position identifier for horizontal and vertical axis. Possible values: center-top, center-center,
     * left-top, right-center and etc.
     *
     * @param int    $width    Crop area width dimension.
     * @param int    $height   Crop area height dimension.
     * @param string $position Position definition (see method description), center-center by default.
     * @return static
     * @throws ImageException
     */
    public function cut($width, $height, $position = 'center-center')
    {
        if (!strpos($position, '-'))
        {
            throw new ImageException('Invalid position definition. Both axis required.');
        }

        list($xAxis, $yAxis) = explode('-', $position);

        return $this->crop(
            $this->offset($xAxis, $width, $this->width()),
            $this->offset($yAxis, $height, $this->height()),
            $width,
            $height
        );
    }

    /**
     * Helper function to calculate axis offset while string identified used to describe crop area position.
     *
     * @param string $position Possible values: center, left/right, top/bottom
     * @param int    $size     Crop area size in this axis.
     * @param int    $length   Axis length.
     * @return int
     */
    protected function offset($position, $size, $length)
    {
        if ($position == 'center')
        {
            return ($length - $size) / 2;
        }

        if ($position == 'right' || $position == 'bottom')
        {
            return $length - $size;
        }

        return 0;
    }

    /**
     * Fix image to specified dimensions, exceed image space can be removed using cut algorythm:
     * center-top, center-center, left-top, right-center and etc.
     *
     * Last argument specifies if method can rotate fit area for better results:
     *
     * Fit area width=800, height=400, target image has dimensions 2000x1000.
     * - Rotate not allowed: image will be resized to 800x400
     * - Rotate allowed: image will be resized to 800x400 (the same)
     *
     * Fit area width=800, height=400, target image has dimensions 1000x2000.
     * - Rotate not allowed: image will be resized to 800x400
     * - Rotate allowed: image will be resized to 400x800 (fit orientation changed)
     *
     * @param int    $width       Fit area width dimension.
     * @param int    $height      Fit area height dimension.
     * @param bool   $crop        Cut all image parts outside fit area.
     * @param string $position    Position definition (see cut() method description), center-center by default.
     * @param bool   $allowRotate Allow rotate of fit area for better results. Not allowed by default.
     * @return static
     */
    public function fit($width, $height, $crop = true, $position = 'center-center', $allowRotate = false)
    {
        if ($allowRotate && (($this->width < $this->height && $width < $height) || ($this->width > $this->height && $width > $height)))
        {
            //Rotate fit dimensions for better results
            list($width, $height) = array($height, $width);
        }

        $ratio = $this->width / $this->height;

        if ($ratio >= ($width / $height))
        {
            $this->resize($ratio * $height, $height);
        }
        else
        {
            $this->resize($width, $width / $ratio);
        }

        if ($crop)
        {
            $this->cut($width, $height, $position);
        }

        return $this;
    }

    /**
     * Fit image to box specified by side size, exceed image space can be removed using cut algorythm:
     * center-top, center-center, left-top, right-center and etc.
     *
     * @param int    $side     Box side size in pixels.
     * @param bool   $crop     Cut all image parts outside fit area.
     * @param string $position Position definition (see cut() method description), center-center by default.
     * @return static
     */
    public function box($side, $crop = true, $position = 'center-center')
    {
        return $this->fit($side, $side, $crop, $position);
    }

    /**
     * Process all image commands and save result to save file which was used for input, JPEG images can also be saved
     * with specified quality.
     *
     * @param int|bool $quality    JPEG quality.
     * @param bool     $removeIPTC Remove IPTC and other metadata.
     * @return static
     */
    public function save($quality = false, $removeIPTC = false)
    {
        $this->processor()->process(realpath($this->filename), $quality, $removeIPTC);

        try
        {
            $this->properties = getimagesize($this->filename, $this->iptc);
        }
        catch (\Exception $exception)
        {
            $this->filename = '';
        }

        return $this;
    }

    /**
     * Process all image commands and save result to specified file, JPEG images can also be saved with specified quality.
     * New ImageObject associated with destination file will be returned, original object will be left untouched.
     *
     * @param string   $filename   Destination filename.
     * @param int|bool $quality    JPEG quality.
     * @param bool     $removeIPTC Remove IPTC and other metadata.
     * @return static
     */
    public function saveAs($filename, $quality = false, $removeIPTC = false)
    {
        $this->processor()->process($filename, $quality, $removeIPTC);

        return static::make(compact('filename'));
    }

    /**
     * Image processor represents operations associated with one specific image file, all processing operation (resize,
     * crop and etc) described via operations sequence and perform on image save, every ImageObject will have it's own
     * processor.
     *
     * Every processor will implement set of pre-defined operations, however additional operations can be supported by
     * processor and extend default set of image manipulations.
     *
     * Changing processor to another type will erase all registered and not performed image commands.
     *
     * @param string $type Forced processor id.
     * @return ProcessorInterface
     */
    public function processor($type = '')
    {
        if ($this->processor)
        {
            return $this->processor;
        }

        return $this->processor = ImageManager::getInstance()->imageProcessor(realpath($this->filename), $type);
    }

    /**
     * Open existed filename and create ImageObject based on it, ImageObject->isSupported() method can be used to verify
     * that file is supported and can be processed. ImageObject preferred to be used for processing existed images, rather
     * that creating new.
     *
     * @param string $filename Local image filename.
     * @return static
     */
    public static function open($filename)
    {
        return static::make(compact('filename'));
    }
}
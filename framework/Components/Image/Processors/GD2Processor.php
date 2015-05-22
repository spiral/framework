<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Image\Processors;

use Spiral\Components\Image\ImageObject;
use Spiral\Components\Image\ProcessorInterface;

abstract class GD2Processor implements ProcessorInterface
{
    /**
     * New image processor instance. Image processor represents operations associated with one
     * specific image file, all processing operation (resize, crop and etc) described via operations
     * sequence and perform on image save, every ImageObject will have it's own processor.
     *
     * Every processor will implement set of pre-defined operations, however additional
     * operations can be supported by processor and extend default set of image manipulations.
     *
     * @param string $filename Local filename.
     * @param array  $options  Processor specific options.
     */
    public function __construct($filename, array $options)
    {
        // TODO: Implement __construct() method.
    }

    /**
     * Command to perform proportional image resize.
     *
     * @param int $width  Maximum image width.
     * @param int $height Maximum image height.
     */
    public function resize($width, $height)
    {
        // TODO: Implement resize() method.
    }

    /**
     * Command to crop image area defined by coordinates and dimensions.
     *
     * @param int $x      Crop area left top X coordinate.
     * @param int $y      Crop area left top Y coordinate.
     * @param int $width  Crop area width dimension.
     * @param int $height Crop area height dimension.
     */
    public function crop($x, $y, $width, $height)
    {
        // TODO: Implement crop() method.
    }

    /**
     * Combine two images together, image file provided in first argument will be placed at top of
     * currently open file, position, width and heights can be specified in arguments.
     *
     * @param string $filename Overlay filename.
     * @param int    $opacity  Opacity (0 - overlay absolutely transparent, 100 - watermark is not
     *                         transparent).
     * @param int    $x        Composited image left top X coordinate.
     * @param int    $y        Composited image left top Y coordinate.
     * @param int    $width    Composited image width.
     * @param int    $height   Composited image height.
     */
    public function composite($filename, $opacity, $x, $y, $width, $height)
    {
        // TODO: Implement composite() method.
    }

    /**
     * Draw line with specified color, width, coordinates and style.
     *
     * @param int    $x1    Left top X coordinate.
     * @param int    $y1    Left top Y coordinate.
     * @param int    $x2    Bottom right X coordinate.
     * @param int    $y2    Bottom right Y coordinate.
     * @param string $color Line color in RGB format.
     * @param int    $width Line width in pixels.
     * @param int    $style Line style, solid and dotted styles supported by default.
     */
    public function line($x1, $y1, $x2, $y2, $color, $width = 1, $style = ImageObject::LINE_SOLID)
    {
        // TODO: Implement line() method.
    }

    /**
     * Draw rectangle width specified stroke style, stoke color, background fill colors and
     * coordinates.
     *
     * @param int    $x1        Left top X coordinate.
     * @param int    $y1        Left top Y coordinate.
     * @param int    $x2        Bottom right X coordinate.
     * @param int    $y2        Bottom right Y coordinate.
     * @param string $fillColor Rectangle fill color in RGB format.
     * @param string $color     Stroke color in RGB format.
     * @param int    $width     Stroke width in pixels.
     * @param int    $style     Stroke style, solid and dotted styles supported by default.
     */
    public function rectangle(
        $x1,
        $y1,
        $x2,
        $y2,
        $fillColor,
        $color,
        $width = 1,
        $style = ImageObject::LINE_SOLID
    )
    {
        // TODO: Implement rectangle() method.
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
     */
    public function annotation($x, $y, $string, $color, $font, $size = 12, $angle = 0)
    {
        // TODO: Implement annotation() method.
    }

    /**
     * Blurring images so they become fuzzy may not seem like a useful operation, but actually is
     * very useful for generating background effects and shadows. It is also very useful for
     * smoothing the effects of the 'jaggies' to anti-alias the edges of images, and to round out
     * features to produce highlighting effects.
     *
     * The important setting in the above is the sigma value. It can be thought of as an
     * approximation of just how much your want the image to 'spread' or blur, in pixels. Think of
     * it as the size of the brush used to blur the image. The numbers are floating point values,
     * so you can use a very small value like '0.5'.
     *
     * @param float $sigma  It can be thought of as an approximation of just how much your want the
     *                      image to 'spread' or blur, in pixels.
     * @param int   $radius Blur radius (0 to detect automatically).
     */
    public function blur($sigma, $radius = 0)
    {
        // TODO: Implement blur() method.
    }

    /**
     * Sharpens an image. We convolve the image with a Gaussian operator of the given radius and
     * standard deviation (sigma).
     *
     * @param float $sigma  The standard deviation of the Gaussian, in pixels.
     * @param int   $radius The radius of the Gaussian, in pixels, not counting the center pixel.
     *                      Use 0 for auto-select.
     */
    public function sharpen($sigma, $radius = 0)
    {
        // TODO: Implement sharpen() method.
    }

    /**
     * Convert image color space to gray scale, will convert all existed colors to black and white
     * representation.
     */
    public function grayScale()
    {
        // TODO: Implement grayScale() method.
    }

    /**
     * Process all image commands and save result to specified file, JPEG images can
     * also be saved with specified quality.
     *
     * @param string $output     Output filename, can be identical to input one.
     * @param int    $quality    JPEG quality.
     * @param bool   $removeIPTC Remove IPTC and other metadata.
     */
    public function process($output, $quality, $removeIPTC = true)
    {
        // TODO: Implement process() method.
    }
}
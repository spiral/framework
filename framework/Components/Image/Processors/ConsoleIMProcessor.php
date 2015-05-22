<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Image\Processors;

use Spiral\Components\Files\FileManager;
use Spiral\Components\Image\ImageException;
use Spiral\Components\Image\ImageObject;
use Spiral\Components\Image\ProcessorInterface;
use Spiral\Core\Component;
use Spiral\Core\Component\LoggerTrait;
use Symfony\Component\Process\Process;

/**
 * Some properties can be accessed via magic methods.
 *
 * @property int    $bitDepth        Bits per pixel, default 8.
 * @property bool   $progressiveJPEG Use interpolate mode during converting JPEGs, disabled by
 *                                   default.
 * @property string $commandPrefix   Command(s) prefix, empty by default. Use "nice" to make
 *                                   converting more polite for CPU.
 * @property bool   $openMP          Multi-thread support, disabled by default.
 */
class ConsoleIMProcessor extends Component implements ProcessorInterface
{
    /**
     * Logging shell queries.
     */
    use LoggerTrait;

    /**
     * ImageMagic converting options, known options. List of options
     * listed in class declaration.
     *
     * @var array
     */
    protected $options = array(
        'bitDepth'        => 8,
        'progressiveJPEG' => false,
        'commandPrefix'   => '',
        'openMP'          => false
    );

    /**
     * Input filename.
     *
     * @var string
     */
    protected $filename = '';

    /**
     * Commands stack.
     *
     * @var array
     */
    protected $commands = array();

    /**
     * FileManager component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * New image processor instance. Image processor represents operations associated with one
     * specific image file, all processing operation (resize, crop and etc) described via operations
     * sequence and perform on image save, every ImageObject will have it's own processor.
     *
     * Every processor will implement set of pre-defined operations, however additional operations
     * can be supported by processor and extend default set of image manipulations.
     *
     * @param string      $filename Local filename.
     * @param array       $options  Processor specific options.
     * @param FileManager $file     FileManager.
     */
    public function __construct($filename, array $options, FileManager $file = null)
    {
        $this->options = $this->options + $options;
        $this->filename = $filename;
        $this->file = !empty($file) ? $file : FileManager::getInstance();
    }

    /**
     * Current imageMagic processor options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Update imageMagic processor options.
     *
     * @param array $options
     * @return static
     */
    public function options(array $options)
    {
        $this->options = $this->options + $options;

        return $this;
    }

    /**
     * Get option value by name.
     *
     * @param string $name
     * @return null|mixed
     */
    public function __get($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Update imageMagic processor option by it's name.
     *
     * @param string $name  Option name.
     * @param mixed  $value Option value.
     * @return static
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Update imageMagic processor option by it's name.
     *
     * @param string $name  Option name.
     * @param mixed  $value Option value.
     * @return static
     */
    public function __set($name, $value)
    {
        return $this->setOption($name, $value);
    }

    /**
     * Add new imageMagic command to stack, commands will be performed in sequence, every command
     * can be additionally merged with drawing.
     *
     * @param string $command     Image Magic command to perform.
     * @param string $application What ImageMagic application should be used, convert by default.
     * @return static
     */
    public function command($command, $application = 'convert')
    {
        $this->commands[] = array(
            'command' =>
                "{$application} {options} {input} {$command}"
                . " {drawing} {quality} {depth} {removeIPTC} {output}",
            'drawing' => array()
        );

        return $this;
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
        return $this->command("-resize {$width}x{$height}");
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
        $x = $x >= 0 ? '+' . (int)$x : (int)$x;
        $y = $y >= 0 ? '+' . (int)$y : (int)$y;

        return $this->command("-crop {$width}x{$height}{$x}{$y}");
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
     * @return static
     */
    public function composite($filename, $x = 0, $y = 0, $width, $height, $opacity)
    {
        $x = $x >= 0 ? '+' . (int)$x : (int)$x;
        $y = $y >= 0 ? '+' . (int)$y : (int)$y;

        //Little bit different order
        return $this->command(
            "",
            "composite $filename -geometry {$width}x{$height}{$x}{$y} -dissolve $opacity"
        );
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
     * @return static
     */
    public function line($x1, $y1, $x2, $y2, $color, $width = 1, $style = ImageObject::LINE_SOLID)
    {
        $lineStyle = '';
        if ($style == ImageObject::LINE_DOTTED)
        {
            $lineStyle = "stroke-dasharray $width $width";
        }

        return $this->drawCommand(
            "-fill \"transparent\" -stroke \"$color\" -strokewidth $width"
            . " -draw \"$lineStyle line $x1,$y1,$x2,$y2\""
        );
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
     * @return static
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
        $lineStyle = '';
        if ($style == ImageObject::LINE_DOTTED)
        {
            $lineStyle = "stroke-dasharray $width $width";
        }

        return $this->drawCommand(
            "-fill \"$fillColor\" -stroke \"$color\" -strokewidth $width"
            . " -draw \"$lineStyle rectangle $x1,$y1,$x2,$y2\""
        );
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
    public function annotation($x, $y, $string, $color, $font, $size = 12, $angle = 0)
    {
        $x = $x >= 0 ? '+' . (int)$x : (int)$x;
        $y = $y >= 0 ? '+' . (int)$y : (int)$y;
        $font = $font ? "-font \"$font\"" : '';

        $string = addcslashes($string, "'\"");

        return $this->drawCommand(
            "-fill \"$color\" -stroke \"transparent\" -pointsize $size $font"
            . " -draw \"rotate $angle text {$x}{$y} '{$string}'\""
        );
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
     * @return static
     */
    public function blur($sigma, $radius = 0)
    {
        return $this->command("-blur {$radius}x{$sigma}");
    }

    /**
     * Sharpens an image. We convolve the image with a Gaussian operator of the given radius and
     * standard deviation (sigma).
     *
     * @param float $sigma  The standard deviation of the Gaussian, in pixels.
     * @param int   $radius The radius of the Gaussian, in pixels, not counting the center pixel.
     *                      Use 0 for auto-select.
     * @return static
     */
    public function sharpen($sigma, $radius = 0)
    {
        return $this->command("-sharpen {$radius}x{$sigma}");
    }

    /**
     * Convert image colorspace to gray scale, will convert all existed colors to black and white
     * representation.
     *
     * @return static
     */
    public function grayScale()
    {
        return $this->command("-colorspace gray");
    }

    /**
     * Add drawing command, command will be automatically merged with last converting command.
     *
     * @param string $command
     * @return static
     */
    public function drawCommand($command)
    {
        //Empty command for drawing
        if (empty($this->commands))
        {
            $this->command("");
        }

        $this->commands[count($this->commands) - 1]['drawing'][] = $command;

        return $this;
    }

    /**
     * Process all image commands and save result to specified file, JPEG images can also be saved
     * with specified quality.
     *
     * @param string $output     Output filename, can be identical to input one.
     * @param int    $quality    JPEG quality.
     * @param bool   $removeIPTC Remove IPTC and other metadata.
     * @throws ImageException
     */
    public function process($output, $quality, $removeIPTC = true)
    {
        //Empty command to transcode file
        if (empty($this->commands))
        {
            $this->command("");
        }

        $processOptions = array();
        if ($this->options['progressiveJPEG'])
        {
            $processOptions[] = '-interlace Plane';
        }

        if ($this->options['openMP'])
        {
            $processOptions[] = '--enable-openmp';
        }

        $filename = $this->filename;
        foreach ($this->commands as $command)
        {
            $options = array(
                'options'    => join(' ', $processOptions),
                'input'      => escapeshellarg($output),
                'output'     => escapeshellarg($output),
                'drawing'    => isset($command['drawing']) ? join(" ", $command['drawing']) : '',
                'quality'    => '',
                'depth'      => '-depth ' . $this->options['bitDepth'],
                'removeIPTC' => ''
            );

            if ($removeIPTC)
            {
                $options['removeIPTC'] = '-strip';
                $removeIPTC = false;
            }

            if (!empty($this->filename))
            {
                $options['input'] = escapeshellarg($this->filename);
                $this->filename = '';
            }

            if (!next($this->commands) && !empty($quality))
            {
                //Last command needs to be done with output quality
                $options['quality'] = "-quality $quality";
            }

            $command = interpolate($this->options['commandPrefix'] . $command['command'], $options);
            self::logger()->info($command);

            benchmark('image::imageMagic', $command);
            $process = new Process($command);
            $process->run();

            if ($error = $process->getErrorOutput())
            {
                self::logger()->error($error);
                throw new ImageException("Unable to process image using ImageMagic console.");
            }

            benchmark('image::imageMagic', $command);

            //File modified outside of PHP
            $this->file->clearCache($output);
        }

        //Flush all commands
        $this->commands = array();
        $this->filename = $filename;
    }
}
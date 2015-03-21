<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Image;

use Spiral\Core\Component;
use Spiral\Helpers\ColorHelper;

class ImageAnalyzer extends Component
{
    /**
     * Current image filename, will be used for benchmarking context.
     *
     * @var string
     */
    protected $filename = '';

    /**
     * Image resource used to read pixel colors.
     *
     * @var null|resource
     */
    protected $image = null;

    /**
     * Image type. Possible values: gif, jpeg, png.
     *
     * @var string
     */
    protected $type = '';

    /**
     * Image width dimension.
     *
     * @var int
     */
    protected $width = 0;

    /**
     * Image height dimension.
     *
     * @var int
     */
    protected $height = 0;

    /**
     * Collected colors per tile.
     *
     * @var array
     */
    public $colorMatrix = array();

    /**
     * Amount of tiles in both image axis.
     *
     * @var int
     */
    protected $dimension = 10;

    /**
     * Color mixing mode.
     */
    const MIX_AVERAGE  = 1;
    const MIX_ADAPTIVE = 2;

    /**
     * Current mixing mode. Mix mode specifies how to detect dominant color in tile, adaptive will
     * select most notable color.
     *
     * @var int
     */
    protected $mixMode = self::MIX_ADAPTIVE;

    /**
     * Create new image colors analyzer. Can be used to fetch dominant image colors or create image
     * colors map.
     *
     * @param string $filename  Local image filename.
     * @param int    $dimension Amount of tiles (chunks) image should be chunked to, more tiles - more
     *                          colors, more time.
     * @param int    $mixMode   Mix mode specifies how to detect dominant color in tile, adaptive
     *                          will select most notable color.
     * @throws ImageException
     */
    public function __construct($filename, $dimension = 15, $mixMode = self::MIX_ADAPTIVE)
    {
        if (!function_exists('getimagesize'))
        {
            throw new ImageException(
                "Unable to find required function 'getimagesize', GD2 extension required."
            );
        }

        $this->filename = $filename;
        $this->dimension = $dimension;
        $this->mixMode = $mixMode;

        try
        {
            $options = getimagesize($filename);
        }
        catch (\Exception $exception)
        {
            throw new ImageException("Unable to open image file.");
        }

        //Supported types: jpeg, png, gif
        switch ($options[2])
        {
            case 1:
                $this->image = imagecreatefromgif($filename);
                $this->type = 'gif';

                break;
            case 2:
                $this->image = imagecreatefromjpeg($filename);
                $this->type = 'jpeg';

                break;
            case 3:
                $this->image = imagecreatefrompng($filename);
                $this->type = 'png';

                break;
        }

        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    /**
     * Collect colors in every image chunk.
     *
     * @return static
     */
    public function analyzeImage()
    {
        benchmark("image::analyzeImage", $this->filename);

        for ($x = 0; $x < $this->dimension; $x++)
        {
            for ($y = 0; $y < $this->dimension; $y++)
            {
                $this->collectColors($x, $y);
            }
        }

        benchmark("image::analyzeImage", $this->filename);

        return $this;
    }

    /**
     * Collecting colors in isolated chunk.
     *
     * @param int $xChunk
     * @param int $yChunk
     */
    protected function collectColors($xChunk, $yChunk)
    {
        $width = ($this->width / $this->dimension);
        $height = ($this->height / $this->dimension);

        $colors = array();
        for ($x = $xChunk * $width; $x < $xChunk * $width + $width; $x++)
        {
            for ($y = $yChunk * $height; $y < $yChunk * $height + $height; $y++)
            {
                if ($x > $this->width || $y > $this->height)
                {
                    continue;
                }

                if ($this->type == 'gif')
                {
                    //Just for pictures with fixed pallete
                    $color = array_values(
                        imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y))
                    );
                }
                else
                {
                    //Color index
                    $color = imagecolorat($this->image, $x, $y);
                    $color = array($color >> 16, ($color >> 8) & 0xFF, $color & 0xFF);
                }

                $colors[] = $color;
            }
        }

        if (!empty($colors))
        {
            $averageColor = $this->mixColors($colors, $this->mixMode);
            $this->colorMatrix[] = ColorHelper::hex($averageColor);
        }
    }

    /**
     * Fetch image dominant colors using ImageColors class, colors will be based on image
     * pixelization representation with specified mixing mode. This is slow function, do not use it
     * in non background operations. Colors will be returned as assosicated array where color is key
     * and percent is value, colors will be sorted from most common to least common color.
     *
     * Use with small images!
     *
     * @param int   $countColors Maximum amount of colors to be fetched.
     * @param float $step        Smaller step - more accurate colors will be detected, but more time
     *                           will be required.
     * @param int   $maxSteps    Maximum amount of steps, to prevent infinite loops.
     * @return array
     */
    public function fetchColors($countColors = 5, $step = 0.005, $maxSteps = 2000)
    {
        benchmark("image::dominantColors", $this->filename);

        $dominant = array();
        foreach ($this->colorMatrix as $color)
        {
            if (isset($dominant[$color]))
            {
                $dominant[$color]++;
                continue;
            }

            $dominant[$color] = 1;
        }

        $dominant = $this->groupColors($dominant, $delta = $step);
        while (count($dominant) > $countColors && $maxSteps--)
        {
            $delta += $step;
            $dominant = $this->groupColors($dominant, $delta);
        }

        $countColors = count($this->colorMatrix);
        foreach ($dominant as &$percent)
        {
            $percent /= $countColors;
            unset($percent);
        }

        arsort($dominant);

        benchmark("image::dominantColors", $this->filename);

        return $dominant;
    }

    /**
     * Grouping colors together. Higher delta value - less colors will be created due more pairs will
     * be counted as same colors.
     *
     * @param array $colors Array of colors.
     * @param float $delta  Maximum distance between colors.
     * @return array
     */
    protected function groupColors(array $colors, $delta = 0.0)
    {
        $result = array();
        foreach ($colors as $color => $count)
        {
            $colorA = ColorHelper::rgb($color);

            $minDelta = 55.6;
            $matchID = null;

            foreach ($result as $rColor => $rCount)
            {
                $colorDelta = ColorHelper::colorsDistance($colorA, ColorHelper::rgb($rColor));

                if ($colorDelta < $minDelta)
                {
                    $minDelta = $colorDelta;
                    $matchID = $rColor;
                }
            }

            if (!empty($matchID) && $minDelta <= $delta)
            {
                if ($count < $result[$matchID])

                {
                    $result[$matchID] = $count + $result[$matchID];
                }
                else
                {
                    $result[$color] = $count + $result[$matchID];
                    unset($result[$matchID]);
                }
            }
            else
            {
                $result[$color] = $count;
            }
        }

        return $result;
    }

    /**
     * Calculate average color in chunk. Average mix mode will simply generate colors using
     * summarized value of other colors, this is fast but not great results, adaptive will use
     * colors most noticeable for human eye.
     *
     * @param array $colors  Array of RGB colors collected from chunk.
     * @param int   $mixMode Mix mode, adaptive and average supported.
     * @return array
     */
    static public function mixColors(array $colors, $mixMode)
    {
        $countColors = count($colors);
        $average = array(0, 0, 0);

        if ($mixMode == self::MIX_AVERAGE)
        {
            foreach ($colors as $color)
            {
                $average[0] += $color[0];
                $average[1] += $color[1];
                $average[2] += $color[2];
            }

            $average[0] = floor($average[0] / $countColors);
            $average[1] = floor($average[1] / $countColors);
            $average[2] = floor($average[2] / $countColors);
        }

        if ($mixMode == self::MIX_ADAPTIVE)
        {
            //Getting average lightness and saturation
            $saturation = 0;
            $lightness = 0;

            $slColors = array();

            foreach ($colors as $color)
            {
                $slColors[] = $SL = ColorHelper::hsl($color);

                $saturation += $SL[1];
                $lightness += $SL[2];
            }

            $saturation /= $countColors;
            $lightness /= $countColors;

            //Moving to HSL colorspace
            foreach ($colors as $index => $color)
            {
                $SL = $slColors[$index];

                if ($SL[1] * $SL[2] >= $saturation * $lightness * 0.95)
                {
                    $average[0] += $color[0];
                    $average[1] += $color[1];
                    $average[2] += $color[2];
                }
                else
                {
                    $countColors--;
                }
            }

            if ($countColors == 0)
            {
                //By average counter
                foreach ($colors as $color)
                {
                    $average[0] += $color[0];
                    $average[1] += $color[1];
                    $average[2] += $color[2];
                }

                $countColors = count($colors);
                $average[0] = floor($average[0] / $countColors);
                $average[1] = floor($average[1] / $countColors);
                $average[2] = floor($average[2] / $countColors);

                //Gray
                return $average;
            }

            $average[0] = floor($average[0] / $countColors);
            $average[1] = floor($average[1] / $countColors);
            $average[2] = floor($average[2] / $countColors);
        }

        return $average;
    }
}
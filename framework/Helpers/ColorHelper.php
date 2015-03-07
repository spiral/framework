<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Helpers;

class ColorHelper
{
    /**
     * Get string HEX representation of RGB color.
     *
     * @param array $rgb
     * @return string
     */
    public static function hex(array $rgb)
    {
        $red = str_pad(dechex($rgb[0] % 256), 2, '0', STR_PAD_LEFT);
        $green = str_pad(dechex($rgb[1] % 256), 2, '0', STR_PAD_LEFT);
        $blue = str_pad(dechex($rgb[2] % 256), 2, '0', STR_PAD_LEFT);

        return strtoupper($red . $green . $blue);
    }

    /**
     * Get color RGB channels by hex representation.
     * Example: getRGB('FFFFF') => (255,255,255)
     *
     * @param string $hex
     * @return array
     */
    public static function rgb($hex)
    {
        $hex = str_split($hex, round(strlen($hex) / 3));

        return array(hexdec($hex[0]), hexdec($hex[1]), hexdec($hex[2]));
    }

    /**
     * Get HSV color representation based on R,G,B channels. hue = (0,360), saturation = (0,1), value = (0,1)
     *
     * @param array $rgb
     * @return array
     */
    public static function hsl(array $rgb)
    {
        $hue = 0;
        $saturation = 0;

        $rgb[0] /= 255;
        $rgb[1] /= 255;
        $rgb[2] /= 255;

        $max = max($rgb[0], $rgb[1], $rgb[2]);
        $min = min($rgb[0], $rgb[1], $rgb[2]);

        $delta = $max - $min;
        $lightness = ($max + $min) / 2;

        if ($delta != 0)
        {
            if ($lightness < 0.5)
            {
                $saturation = $delta / ($max + $min);
            }
            else
            {
                $saturation = $delta / (2 - $max - $min);
            }

            $deltaR = ((($max - $rgb[0]) / 6) + ($delta / 2)) / $delta;
            $deltaG = ((($max - $rgb[1]) / 6) + ($delta / 2)) / $delta;
            $deltaB = ((($max - $rgb[2]) / 6) + ($delta / 2)) / $delta;

            if ($rgb[0] == $max)
            {
                $hue = $deltaB - $deltaG;
            }
            else
            {
                if ($rgb[1] == $max)
                {
                    $hue = (1 / 3) + $deltaR - $deltaB;
                }
                else
                {
                    if ($rgb[2] == $max)
                    {
                        $hue = (2 / 3) + $deltaG - $deltaR;
                    }
                }
            }

            if ($hue < 0)
            {
                $hue++;
            }

            if ($hue > 1)
            {
                $hue--;
            }
        }

        return array($hue, $saturation, $lightness);
    }

    /**
     * Compare two colors (simple distance value will be used).
     *
     * @param array $colorA
     * @param array $colorB
     * @return float
     */
    public static function colorsDistance(array $colorA, array $colorB)
    {
        return sqrt(pow($colorA[0] - $colorB[0], 2) * pow($colorA[1] - $colorB[1], 2) + pow($colorA[2] - $colorB[2], 2)) / 255;
    }
}
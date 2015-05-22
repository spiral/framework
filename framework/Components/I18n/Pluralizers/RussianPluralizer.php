<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\I18n\Pluralizers;

use Spiral\Components\I18n\PluralizerInterface;

class RussianPluralizer implements PluralizerInterface
{
    /**
     * Get abstract pluralization formula. Formula should be created in abstract form where
     * number is "n" and form represented by it's index. Basically formula should be PO compatible.
     *
     * @return string
     */
    public function getFormula()
    {
        return 'n%10==1&&n%100!=11?0:(n%10>=2&&n%10<=4&&(n100<10||n100>=20)?1:2)';
    }

    /**
     * How many forms presented.
     *
     * @return int
     */
    public function countForms()
    {
        return 3;
    }

    /**
     * Get form for specified number.
     *
     * @param int   $number
     * @param array $forms Pluralization forms.
     * @return int
     */
    public function getForm($number, array $forms)
    {
        return ($number % 10 == 1 && $number % 100 != 11)
            ? $forms[0]
            : (
            $number % 10 >= 2 && $number % 10 <= 4 && ($number % 100 < 10 || $number % 100 >= 20)
                ? $forms[1]
                : $forms[2]
            );
    }
}
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

class EnglishPluralizer implements PluralizerInterface
{
    /**
     * Get abstract pluralization formula. Formula should be created in abstract form where
     * number is "n" and form represented by it's index. Basically formula should be PO compatible.
     *
     * @return string
     */
    public function getFormula()
    {
        return 'n==1?0:1';
    }

    /**
     * How many forms presented.
     *
     * @return int
     */
    public function countForms()
    {
        return 2;
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
        return $number == 1 ? $forms[0] : $forms[1];
    }
}
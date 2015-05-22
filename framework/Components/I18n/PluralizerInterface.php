<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\I18n;

interface PluralizerInterface
{
    /**
     * Get abstract pluralization formula. Formula should be created in abstract form where
     * number is "n" and form represented by it's index. Basically formula should be PO compatible.
     *
     * @link http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
     * @return string
     */
    public function getFormula();

    /**
     * How many forms presented.
     *
     * @return int
     */
    public function countForms();

    /**
     * Get form for specified number.
     *
     * @param int   $number
     * @param array $forms Pluralization forms.
     * @return int
     */
    public function getForm($number, array $forms);
}
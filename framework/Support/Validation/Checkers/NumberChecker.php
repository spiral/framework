<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Validation\Checkers;

use Spiral\Support\Validation\Checker;

class NumberChecker extends Checker
{
    /**
     * Set of default error messages associated with their check methods organized by method name.
     * Will be returned by the checker to replace the default validator message. Can have placeholders
     * for interpolation.
     *
     * @var array
     */
    protected $messages = array(
        "range"  => "[[Field '{field}' should be in range or {0}-{1}.]]",
        "higher" => "[[Field '{field}' should be higher than {0}.]]",
        "lower"  => "[[Field '{field}' should be lower than {0}.]]"
    );

    /**
     * Checks value in range including borders. Both borders included into range. Can accept both
     * decimal and real numbers.
     *
     * @param float $value Value to be validated.
     * @param float $begin Beginning or range (included).
     * @param float $end   End of range (included).
     * @return bool
     */
    public function range($value, $begin, $end)
    {
        return $value >= $begin && $value <= $end;
    }

    /**
     * Value is higher than or equal to limit.
     *
     * @param float $value Value to be validated.
     * @param float $limit Lower range.
     * @return bool
     */
    public function higher($value, $limit)
    {
        return $value >= $limit;
    }

    /**
     * Value is lower than or equal to expected.
     *
     * @param float $value Value to be validated.
     * @param float $limit Higher range.
     * @return bool
     */
    public function lower($value, $limit)
    {
        return $value <= $limit;
    }
}
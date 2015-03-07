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

class MixedChecker extends Checker
{
    /**
     * Set of default error messages associated with their check methods organized by method name. Will be returned by
     * the checker to replace the default validator message. Can have placeholders for interpolation.
     *
     * @var array
     */
    protected $messages = array(
        "cardNumber" => "[[Field '{field}' is not valid card number.]]"
    );

    /**
     * Validate credit card number by Luhn algorithm.
     *
     * @link http://en.wikipedia.org/wiki/Luhn_algorithm
     * @param string $cardNumber Card number, can include spaces and other symbols.
     * @return bool
     */
    public function cardNumber($cardNumber)
    {
        if (strlen($cardNumber) < 12)
        {
            return false;
        }

        $result = 0;
        $odd = strlen($cardNumber) % 2;
        preg_replace('/[^0-9]+/', '', $cardNumber);

        for ($i = 0; $i < strlen($cardNumber); $i++)
        {
            $result += $odd ? $cardNumber[$i] : (($cardNumber[$i] * 2 > 9) ? $cardNumber[$i] * 2 - 9 : $cardNumber[$i] * 2);
            $odd = !$odd;
        }

        // Check validity.
        return ($result % 10 == 0) ? true : false;
    }
}
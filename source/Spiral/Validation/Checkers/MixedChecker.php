<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Validation\Checkers;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\Prototypes\AbstractChecker;

/**
 * Validations can't be fitted to any other checker.
 */
class MixedChecker extends AbstractChecker implements SingletonInterface
{
    /**
     * {@inheritdoc}
     */
    const MESSAGES = [
        'cardNumber' => '[[Please enter valid card number.]]',
    ];

    /**
     * Check credit card passed by Luhn algorithm.
     *
     * @link http://en.wikipedia.org/wiki/Luhn_algorithm
     *
     * @param string $cardNumber
     *
     * @return bool
     */
    public function cardNumber(string $cardNumber): bool
    {
        if (!is_string($cardNumber) || strlen($cardNumber) < 12) {
            return false;
        }

        $result = 0;
        $odd = strlen($cardNumber) % 2;
        preg_replace('/[^0-9]+/', '', $cardNumber);

        for ($i = 0; $i < strlen($cardNumber); ++$i) {
            $result += $odd
                ? $cardNumber[$i]
                : (($cardNumber[$i] * 2 > 9) ? $cardNumber[$i] * 2 - 9 : $cardNumber[$i] * 2);

            $odd = !$odd;
        }

        // Check validity.
        return ($result % 10 == 0) ? true : false;
    }

    /**
     * Check if value matches value from another field.
     *
     * @param mixed  $value
     * @param string $field
     * @param bool   $strict
     *
     * @return bool
     */
    public function match($value, string $field, bool $strict = false): bool
    {
        if ($strict) {
            return $value === $this->getValidator()->getValue($field, null);
        }

        return $value == $this->getValidator()->getValue($field, null);
    }
}

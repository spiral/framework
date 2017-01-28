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
 *
 * You must define error message for match method by yourself.
 */
class MixedChecker extends AbstractChecker implements SingletonInterface
{
    /**
     * {@inheritdoc}
     */
    const MESSAGES = [
        'cardNumber' => '[[Please enter valid card number.]]'
    ];

    /**
     * Check credit card passed by Luhn algorithm.
     *
     * @link http://en.wikipedia.org/wiki/Luhn_algorithm
     *
     * @param string $value
     *
     * @return bool
     */
    public function cardNumber($value): bool
    {
        if (!is_string($value) || strlen($value) < 12) {
            return false;
        }

        $result = 0;
        $odd = strlen($value) % 2;
        preg_replace('/[^0-9]+/', '', $value);

        for ($i = 0; $i < strlen($value); ++$i) {
            $result += $odd
                ? $value[$i]
                : (($value[$i] * 2 > 9) ? $value[$i] * 2 - 9 : $value[$i] * 2);

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

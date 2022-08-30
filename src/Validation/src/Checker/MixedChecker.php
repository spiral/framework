<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation\Checker;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\AbstractChecker;

/**
 * @inherit-messages
 */
final class MixedChecker extends AbstractChecker implements SingletonInterface
{
    /**
     * {@inheritdoc}
     */
    public const MESSAGES = [
        'cardNumber' => '[[Please enter valid card number.]]',
        'match'      => '[[Fields {1} and {2} do not match.]]',
    ];

    /**
     * Check credit card passed by Luhn algorithm.
     *
     * @link http://en.wikipedia.org/wiki/Luhn_algorithm
     * @param string $value
     */
    public function cardNumber($value): bool
    {
        if (!is_string($value) || strlen($value) < 12) {
            return false;
        }

        if ($value !== preg_replace('/\D+/', '', $value)) {
            return false;
        }

        $result = 0;
        $odd = strlen($value) % 2;

        $length = strlen($value);
        for ($i = 0; $i < $length; ++$i) {
            $result += $odd
                ? $value[$i]
                : (($value[$i] * 2 > 9) ? $value[$i] * 2 - 9 : $value[$i] * 2);

            $odd = !$odd;
        }

        // Check validity.
        return $result % 10 === 0;
    }

    /**
     * Check if value matches value from another field.
     *
     * @param mixed  $value
     */
    public function match($value, string $field, bool $strict = false): bool
    {
        if ($strict) {
            return $value === $this->getValidator()->getValue($field, null);
        }

        return $value == $this->getValidator()->getValue($field, null);
    }
}

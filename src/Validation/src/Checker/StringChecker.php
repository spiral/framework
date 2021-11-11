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
final class StringChecker extends AbstractChecker implements SingletonInterface
{
    /**
     * {@inheritdoc}
     */
    public const MESSAGES = [
        'regexp'  => '[[Value does not match required pattern.]]',
        'shorter' => '[[Enter text shorter or equal to {1}.]]',
        'longer'  => '[[Text must be longer or equal to {1}.]]',
        'length'  => '[[Text length must be exactly equal to {1}.]]',
        'range'   => '[[Text length should be in range of {1}-{2}.]]',
    ];

    /**
     * Check string using regexp.
     *
     * @param mixed  $value
     */
    public function regexp($value, string $expression): bool
    {
        return is_string($value) && preg_match($expression, $value);
    }

    /**
     * Check if string length is shorter or equal that specified value.
     *
     * @param string $value
     */
    public function shorter($value, int $length): bool
    {
        return is_string($value) && mb_strlen(trim($value)) <= $length;
    }

    /**
     * Check if string length is longer or equal that specified value.
     *
     * @param mixed $value
     */
    public function longer($value, int $length): bool
    {
        return is_string($value) && mb_strlen(trim($value)) >= $length;
    }

    /**
     * Check if string length are equal to specified value.
     *
     * @param mixed $value
     */
    public function length($value, int $length): bool
    {
        return is_string($value) && mb_strlen(trim($value)) === $length;
    }

    /**
     * Check if string length are fits in specified range.
     *
     * @param mixed $value
     */
    public function range($value, int $min, int $max): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $trimmed = trim($value);

        return (mb_strlen($trimmed) >= $min)
            && (mb_strlen($trimmed) <= $max);
    }
}

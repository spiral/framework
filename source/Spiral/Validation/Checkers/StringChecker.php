<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Validation\Checkers;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Validation\Checkers\Traits\NotEmptyTrait;
use Spiral\Validation\Prototypes\AbstractChecker;

/**
 * String validations.
 */
class StringChecker extends AbstractChecker implements SingletonInterface
{
    use NotEmptyTrait;

    /**
     * {@inheritdoc}
     */
    const MESSAGES = [
        'notEmpty' => '[[This value is required.]]',
        'regexp'   => '[[Your value does not match required pattern.]]',
        'shorter'  => '[[Enter text shorter or equal to {0}.]]',
        'longer'   => '[[Your text must be longer or equal to {0}.]]',
        'length'   => '[[Your text length must be exactly equal to {0}.]]',
        'range'    => '[[Text length should be in range of {0}-{1}.]]',
    ];

    /**
     * Check string using regexp.
     *
     * @param string $value
     * @param string $expression
     *
     * @return bool
     */
    public function regexp($value, string $expression): bool
    {
        return is_string($value)
            && preg_match($expression, $value);
    }

    /**
     * Check if string length is shorter or equal that specified value.
     *
     * @param string $value
     * @param int    $length
     *
     * @return bool
     */
    public function shorter($value, int $length): bool
    {
        return is_string($value)
            && mb_strlen(trim($value)) <= $length;
    }

    /**
     * Check if string length is longer or equal that specified value.
     *
     * @param string $value
     * @param int    $length
     *
     * @return bool
     */
    public function longer($value, int $length): bool
    {
        return is_string($value)
            && mb_strlen(trim($value)) >= $length;
    }

    /**
     * Check if string length are equal to specified value.
     *
     * @param string $value
     * @param int    $length
     *
     * @return bool
     */
    public function length($value, int $length): bool
    {
        return is_string($value)
            && mb_strlen(trim($value)) == $length;
    }

    /**
     * Check if string length are fits in specified range.
     *
     * @param string $value
     * @param int    $lengthA
     * @param int    $lengthB
     *
     * @return bool
     */
    public function range($value, int $lengthA, int $lengthB): bool
    {
        return is_string($value)
            && (mb_strlen($trimmed = trim($value)) >= $lengthA)
            && (mb_strlen($trimmed) <= $lengthB);
    }
}

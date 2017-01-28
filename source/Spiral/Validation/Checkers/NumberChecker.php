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
 * Scalar number validations.
 */
class NumberChecker extends AbstractChecker implements SingletonInterface
{
    /**
     * {@inheritdoc}
     */
    const MESSAGES = [
        'range'  => '[[Your value should be in range of {0}-{1}.]]',
        'higher' => '[[Your value should be higher than {0}.]]',
        'lower'  => '[[Your value should be lower than {0}.]]',
    ];

    /**
     * Check if number in specified range.
     *
     * @param float|int $value
     * @param float|int $begin
     * @param float|int $end
     *
     * @return bool
     */
    public function range($value, $begin, $end): bool
    {
        return is_numeric($value)
            && $value >= $begin
            && $value <= $end;
    }

    /**
     * Check if value is bigger or equal that specified.
     *
     * @param float|int $value
     * @param float|int $limit
     *
     * @return bool
     */
    public function higher($value, $limit): bool
    {
        return is_numeric($value)
            && $value >= $limit;
    }

    /**
     * Check if value smaller of equal that specified.
     *
     * @param float|int $value
     * @param float|int $limit
     *
     * @return bool
     */
    public function lower($value, $limit): bool
    {
        return is_numeric($value)
            && $value <= $limit;
    }
}

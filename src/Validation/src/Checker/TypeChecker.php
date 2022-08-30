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
use Spiral\Validation\Checker\Traits\NotEmptyTrait;

/**
 * @inherit-messages
 */
final class TypeChecker extends AbstractChecker implements SingletonInterface
{
    use NotEmptyTrait;

    /**
     * {@inheritdoc}
     */
    public const MESSAGES = [
        'notNull'  => '[[This value is required.]]',
        'notEmpty' => '[[This value is required.]]',
        'boolean'  => '[[Not a valid boolean.]]',
        'datetime' => '[[Not a valid datetime.]]',
        'timezone' => '[[Not a valid timezone.]]',
    ];

    /**
     * {@inheritdoc}
     */
    public const ALLOW_EMPTY_VALUES = ['notEmpty', 'notNull'];

    /**
     * Value should not be null.
     *
     * @param mixed $value
     */
    public function notNull($value): bool
    {
        return $value !== null;
    }

    /**
     * Value has to be boolean or integer[0,1].
     *
     * @param mixed $value
     */
    public function boolean($value): bool
    {
        return is_bool($value) || (is_numeric($value) && ($value === 0 || $value === 1));
    }

    /**
     * Value has to be valid datetime definition including numeric timestamp.
     *
     * @param mixed $value
     * @deprecated Use \Spiral\Validation\Checker\DatetimeChecker::valid(). Be aware that empty values are now valid.
     */
    public function datetime($value): bool
    {
        if (!is_scalar($value)) {
            return false;
        }

        if (is_numeric($value)) {
            return true;
        }

        return (int)strtotime($value) !== 0;
    }

    /**
     * Value has to be valid timezone.
     *
     * @param mixed $value
     * @deprecated Use \Spiral\Validation\Checker\DatetimeChecker::timezone().
     */
    public function timezone($value): bool
    {
        return in_array((string)$value, \DateTimeZone::listIdentifiers(), true);
    }
}

<?php

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

    public const MESSAGES = [
        'notNull'  => '[[This value is required.]]',
        'notEmpty' => '[[This value is required.]]',
        'boolean'  => '[[Not a valid boolean.]]',
        'datetime' => '[[Not a valid datetime.]]',
        'timezone' => '[[Not a valid timezone.]]',
    ];

    public const ALLOW_EMPTY_VALUES = ['notEmpty', 'notNull'];

    /**
     * Value should not be null.
     */
    public function notNull(mixed $value): bool
    {
        return $value !== null;
    }

    /**
     * Value has to be boolean or integer[0,1].
     */
    public function boolean(mixed $value): bool
    {
        return \is_bool($value) || (\is_numeric($value) && ($value === 0 || $value === 1));
    }
}

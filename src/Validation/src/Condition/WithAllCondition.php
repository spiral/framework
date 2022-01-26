<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation\Condition;

use Spiral\Validation\AbstractCondition;
use Spiral\Validation\ValidatorInterface;

/**
 * Fires when all of listed values are not empty.
 */
final class WithAllCondition extends AbstractCondition
{
    /**
     * @param mixed              $value
     */
    public function isMet(ValidatorInterface $validator, string $field, $value): bool
    {
        foreach ($this->options as $option) {
            if (empty($validator->getValue($option))) {
                return false;
            }
        }

        return true;
    }
}

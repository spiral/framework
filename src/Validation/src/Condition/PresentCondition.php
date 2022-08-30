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
 * Passes when all of the fields are explicitly provided in the request.
 */
final class PresentCondition extends AbstractCondition
{
    /**
     * @param mixed              $value
     */
    public function isMet(ValidatorInterface $validator, string $field, $value): bool
    {
        foreach ($this->options as $option) {
            if (!$validator->hasValue($option)) {
                return false;
            }
        }

        return true;
    }
}

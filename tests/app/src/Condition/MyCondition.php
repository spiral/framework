<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App\Condition;

use Spiral\Validation\AbstractCondition;
use Spiral\Validation\ValidatorInterface;

class MyCondition extends AbstractCondition
{
    public function isMet(ValidatorInterface $validator, string $field, $value): bool
    {
        return $value === $field;
    }
}

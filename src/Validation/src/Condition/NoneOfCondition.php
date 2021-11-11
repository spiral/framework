<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\Validation\Condition;

use Spiral\Validation\AbstractCondition;
use Spiral\Validation\ValidatorInterface;

class NoneOfCondition extends AbstractCondition
{
    /** @var Compositor */
    private $compositor;

    public function __construct(Compositor $compositor)
    {
        $this->compositor = $compositor;
    }

    /**
     * @inheritdoc
     */
    public function isMet(ValidatorInterface $validator, string $field, $value): bool
    {
        foreach ($this->compositor->makeConditions($field, $this->options) as $condition) {
            if ($condition->isMet($validator, $field, $value)) {
                return false;
            }
        }

        return true;
    }
}

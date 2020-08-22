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

class AnyOfCondition extends AbstractCondition
{
    /** @var Compositor */
    private $compositor;

    /**
     * @param Compositor $compositor
     */
    public function __construct(Compositor $compositor)
    {
        $this->compositor = $compositor;
    }

    /**
     * @inheritdoc
     */
    public function isMet(ValidatorInterface $validator, string $field, $value): bool
    {
        if (empty($this->options)) {
            return true;
        }

        foreach ($this->compositor->makeConditions($field, $this->options) as $condition) {
            if ($condition->isMet($validator, $field, $value)) {
                return true;
            }
        }

        return false;
    }
}

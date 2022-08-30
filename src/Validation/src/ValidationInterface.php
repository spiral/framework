<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Validation;

/**
 * Creates validators with given rules and data.
 */
interface ValidationInterface
{
    /**
     * Create validator for given parameters.
     *
     * @param array|\ArrayAccess $data    Target validation data.
     * @param array              $rules   List of associated validation rules (see Rule).
     * @param mixed              $context Validation context (available for checkers and validation
     *                                    methods but is not validated).
     */
    public function validate($data, array $rules, $context = null): ValidatorInterface;
}

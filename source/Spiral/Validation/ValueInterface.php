<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Validation;

/**
 * Some objects can be validated by providing it's value in scalar/array form.
 */
interface ValueInterface
{
    /**
     * Convert object data into simple value (array or string for example).
     *
     * @return mixed
     */
    public function packValue();
}

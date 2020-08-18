<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Spiral\Models\DataEntity;

class NullableEntity extends DataEntity
{
    protected const FILLABLE = '*';
    protected const SETTERS  = ['id' => 'intval'];

    protected function isNullable(string $field): bool
    {
        if (parent::isNullable($field)) {
            return true;
        }

        return $field == 'id';
    }
}

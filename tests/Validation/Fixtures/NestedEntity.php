<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Validation\Fixtures;

use Spiral\Validation\ValidatesEntity;

class NestedEntity extends ValidatesEntity
{
    const VALIDATES = [
        'thing' => ['notEmpty', 'numeric']
    ];
}
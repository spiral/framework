<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Validation\Fixtures;

use Spiral\Validation\ValidatesEntity;

class SampleEntity extends ValidatesEntity
{
    const VALIDATES = [
        'value'  => ['notEmpty'],
        'string' => [
            'notEmpty',
            ['string:shorter', 10]
        ]
    ];
}
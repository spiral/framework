<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication\Database;

use Spiral\Models\Traits\TimestampsTrait;
use Spiral\ORM\Record;

class SampleRecord extends Record
{
    use TimestampsTrait;

    const SCHEMA = [
        'id'            => 'primary',
        'time_altered'  => 'datetime',
        'time_nullable' => 'datetime',
        'value'         => 'string'
    ];

    const DEFAULTS = [
        'time_nullable' => null
    ];
}
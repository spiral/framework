<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace TestApplication\Database;

use MongoDB\BSON\UTCDateTime;
use Spiral\Models\Traits\TimestampsTrait;
use Spiral\ODM\Document;
use Spiral\ODM\Traits\SourceTrait;

class SampleDocument extends Document
{
    use TimestampsTrait, SourceTrait;

    const SCHEMA = [
        'value'         => 'string',
        'time_altered'  => 'timestamp',
        'time_nullable' => UTCDateTime::class,
    ];

    const DEFAULTS = [
        'time_nullable' => null
    ];
}
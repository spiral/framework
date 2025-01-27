<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Spiral\Models\DataEntity;

class BadAccessedEntity extends DataEntity
{
    protected const FILLABLE  = '*';
    protected const ACCESSORS = [
        'name' => NameAccessorX::class,
    ];
}

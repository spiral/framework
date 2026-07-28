<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Spiral\Models\DataEntity;

final class FilteredEntity extends DataEntity
{
    protected const FILLABLE = ['id'];
    protected const SETTERS  = ['id' => 'intval'];
}

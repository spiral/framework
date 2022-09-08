<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Spiral\Models\SchematicEntity;

class TestModel extends SchematicEntity
{
    protected $fillable = ['value'];
    protected $setters  = ['value' => 'intval'];
    protected $getters  = ['value' => 'intval'];
    protected $secured  = '*';

    protected function methodA(): void
    {
    }
}

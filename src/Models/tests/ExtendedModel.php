<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

class ExtendedModel extends TestModel
{
    protected $fillable = ['name'];
    protected $setters  = ['name' => 'strval'];
    protected $getters  = ['name' => 'strtoupper'];
    protected $secured  = ['name'];

    protected function methodB(): void
    {
    }
}

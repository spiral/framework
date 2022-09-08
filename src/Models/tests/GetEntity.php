<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Mockery\Exception\RuntimeException;
use Spiral\Models\DataEntity;

class GetEntity extends DataEntity
{
    protected const GETTERS = ['id' => [self::class, 'filter']];

    protected static function filter($v)
    {
        if (is_array($v)) {
            throw new RuntimeException("can't be array");
        }

        return (int)$v;
    }
}

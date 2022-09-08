<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Spiral\Models\DataEntity;

class PublicEntity extends DataEntity
{
    protected const FILLABLE = '*';

    public function getKeys(): array
    {
        return parent::getKeys();
    }
}

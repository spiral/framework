<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Spiral\Models\DataEntity;

class SecuredEntity extends DataEntity
{
    protected const SECURED = '*';
}

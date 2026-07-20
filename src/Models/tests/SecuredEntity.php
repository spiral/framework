<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Spiral\Models\DataEntity;

final class SecuredEntity extends DataEntity
{
    protected const SECURED = '*';
}

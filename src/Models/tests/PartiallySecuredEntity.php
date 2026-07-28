<?php

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Spiral\Models\DataEntity;

final class PartiallySecuredEntity extends DataEntity
{
    protected const SECURED = ['name'];
}

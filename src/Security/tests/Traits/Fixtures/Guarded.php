<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Traits\Fixtures;

use Spiral\Security\Traits\GuardedTrait;

class Guarded
{
    use GuardedTrait {
        allows as public;
        denies as public;
        resolvePermission as public;
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Tests\Security\Traits\Fixtures;

class GuardedWithNamespace extends Guarded
{
    public const GUARD_NAMESPACE = 'test';
}

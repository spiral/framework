<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\Domain\Annotation\Guarded;
use Spiral\Domain\Annotation\GuardNamespace;

#[GuardNamespace(namespace: '')]
class Demo3Controller
{
    #[Guarded(permission: '')]
    public function do(): string
    {
        return 'ok';
    }
}

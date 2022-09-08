<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\Domain\Annotation\Guarded;
use Spiral\Domain\Annotation\GuardNamespace;

/**
 * @GuardNamespace(namespace="")
 */
class Demo3Controller
{
    /**
     * @Guarded(permission="")
     * @return string
     */
    public function do()
    {
        return 'ok';
    }
}

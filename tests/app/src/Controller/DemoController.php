<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\Domain\Annotation\Guarded;

class DemoController
{
    #[Guarded]
    public function guardedButNoName(): string
    {
        return 'ok';
    }

    #[Guarded('do')]
    public function do(): string
    {
        return 'ok';
    }
}

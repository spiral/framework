<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\Domain\Annotation\Guarded;
use Spiral\Domain\Annotation\GuardNamespace;

#[GuardNamespace('demo')]
class Demo2Controller
{
    #[Guarded('do')]
    public function do1(): string
    {
        return 'ok';
    }

    #[Guarded('do', else: 'notFound')]
    public function do2(): string
    {
        return 'ok';
    }

    #[Guarded('do', else: 'error')]
    public function do3(): string
    {
        return 'ok';
    }

    #[Guarded('do', else: 'badAction')]
    public function do4(): string
    {
        return 'ok';
    }
}

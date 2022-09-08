<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\Domain\Annotation\Guarded;

class DemoController
{
    /**
     * @Guarded()
     * @return string
     */
    public function guardedButNoName()
    {
        return 'ok';
    }

    /**
     * @return string
     */
    #[Guarded()]
    public function guardedButNoNameAttribute()
    {
        return 'ok';
    }

    /**
     * @Guarded("do")
     * @return string
     */
    public function do()
    {
        return 'ok';
    }

    /**
     * @return string
     */
    #[Guarded(permission: 'do')]
    public function doAttribute()
    {
        return 'ok';
    }
}

<?php

declare(strict_types=1);

namespace Spiral\App\Controller;

use Spiral\App\Interceptor;
use Spiral\Domain\Annotation\Pipeline;

class InterceptedController
{
    public function without(): array
    {
        return [__FUNCTION__];
    }

    #[Pipeline(pipeline: [Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class])]
    public function with(): array
    {
        return [__FUNCTION__];
    }

    #[Pipeline(pipeline: [Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class])]
    public function mix(): array
    {
        return [__FUNCTION__];
    }

    #[Pipeline(pipeline: [Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class])]
    public function dup(): array
    {
        return [__FUNCTION__];
    }

    #[Pipeline(pipeline: [Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class], skipNext: true)]
    public function skip(): array
    {
        return [__FUNCTION__];
    }

    #[Pipeline(pipeline: [Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class], skipNext: true)]
    public function first(): array
    {
        return [__FUNCTION__];
    }
}

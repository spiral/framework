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

    /**
     * @Pipeline(pipeline={Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class})
     * @return array
     */
    public function with(): array
    {
        return [__FUNCTION__];
    }

    /**
     * @Pipeline(pipeline={Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class})
     * @return array
     */
    public function mix(): array
    {
        return [__FUNCTION__];
    }

    /**
     * @Pipeline(pipeline={Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class})
     * @return array
     */
    public function dup(): array
    {
        return [__FUNCTION__];
    }

    /**
     * @Pipeline(pipeline={Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class}, skipNext=true)
     * @return array
     */
    public function skip(): array
    {
        return [__FUNCTION__];
    }

    /**
     * @Pipeline(pipeline={Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class}, skipNext=true)
     * @return array
     */
    public function first(): array
    {
        return [__FUNCTION__];
    }

    /**
     * @return array
     */
    #[Pipeline(pipeline: [Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class])]
    public function withAttribute(): array
    {
        return [__FUNCTION__];
    }

    /**
     * @return array
     */
    #[Pipeline(pipeline: [Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class])]
    public function mixAttribute(): array
    {
        return [__FUNCTION__];
    }

    /**
     * @return array
     */
    #[Pipeline(pipeline: [Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class])]
    public function dupAttribute(): array
    {
        return [__FUNCTION__];
    }

    /**
     * @return array
     */
    #[Pipeline(pipeline: [Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class], skipNext: true)]
    public function skipAttribute(): array
    {
        return [__FUNCTION__];
    }

    /**
     * @return array
     */
    #[Pipeline([Interceptor\One::class, Interceptor\Two::class, Interceptor\Three::class], true)]
    public function firstAttribute(): array
    {
        return [__FUNCTION__];
    }
}

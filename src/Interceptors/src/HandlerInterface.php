<?php

declare(strict_types=1);

namespace Spiral\Interceptors;

use Spiral\Interceptors\Context\CallContextInterface;

interface HandlerInterface
{
    /**
     * @throws \Throwable
     */
    public function handle(CallContextInterface $context): mixed;
}

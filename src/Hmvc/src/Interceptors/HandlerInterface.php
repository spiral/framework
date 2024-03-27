<?php

declare(strict_types=1);

namespace Spiral\Interceptors;

use Spiral\Interceptors\Context\CallContext;

interface HandlerInterface
{
    public function handle(CallContext $context): mixed;
}

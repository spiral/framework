<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Context\CallContext;

interface HandlerInterface
{
    public function handle(CallContext $context): mixed;
}

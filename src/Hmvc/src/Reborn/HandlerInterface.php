<?php

declare(strict_types=1);

namespace Spiral\Core\Reborn;

use Spiral\Core\Context\CallContext;

interface HandlerInterface
{
    public function handle(CallContext $context): mixed;
}

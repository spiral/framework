<?php

declare(strict_types=1);

namespace Spiral\Core\Reborn;

use Spiral\Core\Context\CallContextInterface;

interface InterceptorInterface
{
    public function intercept(CallContextInterface $context, HandlerInterface $handler): mixed;
}

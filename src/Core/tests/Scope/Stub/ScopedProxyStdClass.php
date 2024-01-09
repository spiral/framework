<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

use Spiral\Core\Attribute\Proxy;

final class ScopedProxyStdClass
{
    public function __construct(
        #[Proxy] public ContextInterface $context,
    ) {
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }
}

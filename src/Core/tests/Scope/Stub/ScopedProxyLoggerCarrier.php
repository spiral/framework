<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

use Spiral\Core\Attribute\Proxy;

final class ScopedProxyLoggerCarrier
{
    public function __construct(
        #[Proxy] public LoggerInterface $logger,
    ) {}

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}

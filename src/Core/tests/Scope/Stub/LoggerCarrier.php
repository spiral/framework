<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

final class LoggerCarrier
{
    public function __construct(
        public LoggerInterface $logger,
    ) {
    }
}

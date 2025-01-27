<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

final class DatetimeCarrier
{
    public function __construct(
        public \DateTimeInterface $logger,
    ) {}
}

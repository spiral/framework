<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute\Stub;

use Spiral\Queue\Attribute\JobHandler;

#[JobHandler(type: 'test')]
final class JobHandlerAttribute
{
}

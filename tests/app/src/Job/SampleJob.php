<?php

declare(strict_types=1);

namespace Spiral\App\Job;

use Spiral\Queue\JobHandler;

final class SampleJob extends JobHandler
{
    public function invoke(TaskInterface $task): void
    {
    }
}

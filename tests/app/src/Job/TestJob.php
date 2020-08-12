<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\App\Job;

use Spiral\Boot\EnvironmentInterface;
use Spiral\Jobs\JobHandler;

class TestJob extends JobHandler
{
    public function invoke(EnvironmentInterface $env): void
    {
        $env->set('FIRED', true);
    }
}

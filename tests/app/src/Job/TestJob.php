<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App\Job;

use Spiral\Boot\EnvironmentInterface;
use Spiral\Jobs\AbstractJob;

class TestJob extends AbstractJob
{
    public function do(EnvironmentInterface $env)
    {
        $env->set('FIRED', true);
    }
}

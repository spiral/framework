<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\App\Job;

use Spiral\Jobs\JobHandler;

class ErrorJob extends JobHandler
{
    public function invoke()
    {
        throw new \ErrorException("bad job");
    }
}

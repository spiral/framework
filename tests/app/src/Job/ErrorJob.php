<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App\Job;

use Spiral\Jobs\AbstractJob;

class ErrorJob extends AbstractJob
{
    public function do()
    {
        throw new \ErrorException("bad job");
    }
}
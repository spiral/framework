<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\App\Command;

use Spiral\Console\Command;

class DeadCommand extends Command
{
    const NAME = "dead";

    public function perform()
    {
        echo $undefined;
    }
}

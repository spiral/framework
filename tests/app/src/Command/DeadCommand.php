<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\App\Command;

use Spiral\Console\Command;

class DeadCommand extends Command
{
    public const NAME = 'dead';

    public function perform(): void
    {
        echo $undefined;
    }
}

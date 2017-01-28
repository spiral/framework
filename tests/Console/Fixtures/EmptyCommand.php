<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Console\Fixtures;

use Spiral\Console\Command;

class EmptyCommand extends Command
{
    const NAME        = 'empty';
    const DESCRIPTION = 'description';

    public function getAsk()
    {
        return $this->ask();
    }
}
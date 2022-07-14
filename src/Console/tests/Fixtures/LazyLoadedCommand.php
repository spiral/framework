<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures;

use Exception;
use Spiral\Console\Command;

class LazyLoadedCommand extends Command
{
    public const NAME = 'lazy';
    public const DESCRIPTION = 'Lazy description';

    public function perform(): int
    {
        $this->write('OK');

        return self::SUCCESS;
    }
}

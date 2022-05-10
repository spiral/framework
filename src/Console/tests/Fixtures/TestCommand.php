<?php

/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures;

use Spiral\Console\Command;
use Spiral\Core\Container\SingletonInterface;

class TestCommand extends Command implements SingletonInterface
{
    public const NAME = 'test';
    public const DESCRIPTION = 'Test Command';

    private $count = 0;

    public function perform(): void
    {
        $this->write('Hello World - '.($this->count++));
    }
}

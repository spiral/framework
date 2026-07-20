<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures\User;

use Spiral\Console\Command;

class UserCommand extends Command
{
    public const NAME        = 'test:user';
    public const DESCRIPTION = 'Test Command';

    private $count = 0;

    public function perform(): void
    {
        $this->write('Hello User');
    }
}

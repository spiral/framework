<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures;

use Spiral\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class OptionalCommand extends Command
{
    public const NAME = 'optional';

    /**
     * {@inheritDoc}
     */
    public const OPTIONS = [
        ['option', 'o', InputOption::VALUE_NONE, 'Use option'],
    ];

    /**
     * {@inheritDoc}
     */
    public const ARGUMENTS = [
        ['arg', InputArgument::OPTIONAL, 'Value'],
    ];

    public function perform(): void
    {
        $this->write(!$this->option('option') ? 'no option' : $this->argument('arg'));
    }
}

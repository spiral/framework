<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures;

use Spiral\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class HelperCommand extends Command
{
    public const NAME = 'helper';

    /**
     * {@inheritdoc}
     */
    public const ARGUMENTS = [
        ['helper', InputArgument::REQUIRED, 'Helper'],
    ];

    public function perform(): void
    {
        switch ($this->argument('helper')) {
            case 'verbose':
                $this->write($this->isVerbose() ? 'true' : 'false');
                break;

            case 'sprintf':
                $this->sprintf('%s world', 'hello');
                break;

            case 'writeln':
                $this->writeln('hello');
                break;

            case 'table':
                $table = $this->table(['id', 'value']);
                $table->addRow(['1', 'true']);
                $table->render();
        }
    }
}

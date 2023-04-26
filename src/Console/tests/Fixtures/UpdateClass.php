<?php

declare(strict_types=1);

namespace Spiral\Tests\Console\Fixtures;

use Spiral\Tests\Console\ShortException;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateClass
{
    public function do(OutputInterface $output): void
    {
        $output->write('OK');
    }

    public function err(OutputInterface $output): void
    {
        throw new ShortException('Failed configure command');
    }
}

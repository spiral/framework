<?php

declare(strict_types=1);

namespace Spiral\Console\Event;

use Spiral\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandStarting
{
    public function __construct(
        public readonly Command $command,
        public readonly InputInterface $input,
        public readonly OutputInterface $output
    ) {
    }
}

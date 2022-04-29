<?php

declare(strict_types=1);

namespace Spiral\Console\Sequence;

use Spiral\Console\SequenceInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSequence implements SequenceInterface
{
    public function __construct(
        private readonly string $header,
        private readonly string $footer
    ) {
    }

    public function writeHeader(OutputInterface $output): void
    {
        if (!empty($this->header)) {
            $output->writeln($this->header);
        }
    }

    public function writeFooter(OutputInterface $output): void
    {
        if (!empty($this->footer)) {
            $output->writeln($this->footer);
        }
    }
}

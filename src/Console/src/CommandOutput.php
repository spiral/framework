<?php

declare(strict_types=1);

namespace Spiral\Console;

use Symfony\Component\Console\Output\OutputInterface;

final class CommandOutput
{
    public function __construct(
        private readonly int $code,
        private readonly OutputInterface $output
    ) {
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}

<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console;

use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class CommandOutput
{
    private int $code = 0;

    /** @var OutputInterface */
    private $output = '';

    public function __construct(int $code, OutputInterface $output)
    {
        $this->code = $code;
        $this->output = $output;
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

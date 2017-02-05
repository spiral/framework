<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Console;

use Spiral\Core\Component;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Simple command output wrapper. Will be returned as output from ConsoleDispatcher->command method.
 */
class CommandOutput extends Component
{
    /**
     * @var int
     */
    private $code = 0;

    /**
     * @var OutputInterface
     */
    private $output = '';

    /**
     * @param int             $code
     * @param OutputInterface $output
     */
    public function __construct(int $code, OutputInterface $output)
    {
        $this->code = $code;
        $this->output = $output;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return OutputInterface|BufferedOutput
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Console;

use Spiral\Core\Component;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CommandOutput extends Component
{
    /**
     * Code returned by command.
     *
     * @var int
     */
    protected $code = 0;

    /**
     * OutputInterface is the interface implemented by all Output classes.
     *
     * @var OutputInterface
     */
    protected $output = '';

    /**
     * Helper class to wrap command calls outside console environment.
     *
     * @param int             $code
     * @param OutputInterface $output
     */
    public function __construct($code, OutputInterface $output)
    {
        $this->code = $code;
        $this->output = $output;
    }

    /**
     * Get command return code.
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * OutputInterface is the interface implemented by all Output classes.
     *
     * @return OutputInterface|BufferedOutput
     */
    public function getOutput()
    {
        return $this->output;
    }
}
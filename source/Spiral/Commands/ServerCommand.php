<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands;

use Spiral\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

/**
 * Create php development server on specified host and port.
 */
class ServerCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'server';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Run Spiral Development server on specified host and port.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['host', InputArgument::OPTIONAL, 'Host name.', 'localhost']
    ];

    /**
     * {@inheritdoc}
     */
    protected $options = [
        ['port', 'p', InputOption::VALUE_OPTIONAL, 'Port number.', 8080],
        ['timeout', 't', InputOption::VALUE_OPTIONAL, 'Timeout to hang out server.', 36000],
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        $host = $this->argument('host') . ':' . $this->option('port');

        $this->writeln("<info>Spiral Development server started at <comment>{$host}</comment></info>");
        $this->writeln("Press <comment>Ctrl-C</comment> to quit.");

        $process = new Process(
            '"' . PHP_BINARY . '" -S ' . $host . ' "' . directory('framework') . '/../server.php"',
            directory('root'),
            null,
            null,
            $this->option('timeout')
        );

        $process->run(function ($type, $data)
        {
            if ($type != Process::ERR)
            {
                //First character contains request type, second is space
                ($data[0] == 'S' || $this->isVerbose()) && $this->writeln(substr($data, 2));
            }
        });
    }
}
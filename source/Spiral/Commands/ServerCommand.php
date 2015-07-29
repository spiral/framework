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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ServerCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'server';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Run Spiral Development server on specified host and port.';

    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = [
        ['host', InputArgument::OPTIONAL, 'Host name.', 'localhost']
    ];

    /**
     * Command options specified in Symphony format. For more complex definitions redefine getOptions()
     * method.
     *
     * @var array
     */
    protected $options = [
        ['port', 'p', InputOption::VALUE_OPTIONAL, 'Port number.', 8080],
        ['timeout', 't', InputOption::VALUE_OPTIONAL, 'Timeout to hang out server.', 36000],
    ];

    /**
     * Running server.
     */
    public function perform()
    {
        $host = $this->argument('host') . ':' . $this->option('port');

        $this->writeln("<info>Starting Spiral Development server at <comment>{$host}</comment></info>");
        $this->writeln("Press <comment>Ctrl-C</comment> to quit.");

        $process = new Process(
            '"' . PHP_BINARY . '" -S ' . $host . ' "' . directory('framework') . '/server.php"',
            directory('root'),
            null,
            null,
            $this->option('timeout')
        );

        $process->run(function ($type, $data)
        {
            if (Process::ERR != $type)
            {
                //First character contains request type
                $type = $data[0];
                $data = substr($data, 2);

                ($type == 'S' || $this->isVerbose()) && $this->writeln($data);
            }
        });
    }
}
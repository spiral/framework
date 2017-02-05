<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands;

use Spiral\Console\Command;
use Spiral\Core\DirectoriesInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

/**
 * Create php development server on specified host and port.
 *
 * To specify process user:
 *
 * @see https://www.reddit.com/r/PHP/comments/3vlnzq/a_tip_for_those_directly_running_php_s/
 */
class ServerCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'server';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Run development server on specified host and port';

    /**
     * {@inheritdoc}
     */
    const ARGUMENTS = [
        ['host', InputArgument::OPTIONAL, 'Host name', 'localhost']
    ];

    /**
     * {@inheritdoc}
     */
    const OPTIONS = [
        ['port', 'p', InputOption::VALUE_OPTIONAL, 'Port number', 8080],
        ['timeout', 't', InputOption::VALUE_OPTIONAL, 'Timeout to hang out server', 36000],
    ];

    /**
     * @param DirectoriesInterface $directories
     */
    public function perform(DirectoriesInterface $directories)
    {
        $host = $this->argument('host') . ':' . $this->option('port');

        $this->writeln("<info>Development server started at <comment>{$host}</comment></info>");
        $this->writeln("Press <comment>Ctrl-C</comment> to quit.");

        $process = new Process(
            '"' . PHP_BINARY . "\" -S {$host} \"{$directories->directory('framework')}../server.php\"",
            $directories->directory('public'),
            null,
            null,
            $this->option('timeout')
        );

        $process->run(function ($type, $data) {
            if ($type != Process::ERR) {
                //First character contains request type, second is space
                if ($data[0] == 'S' || $this->isVerbosity()) {
                    $this->writeln(substr($data, 2));
                }
            }
        });
    }
}
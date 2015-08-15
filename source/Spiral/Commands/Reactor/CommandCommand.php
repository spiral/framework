<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor;

use Spiral\Console\Command;
use Spiral\Reactor\Generators\CommandGenerator;
use Spiral\Reactor\Reactor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create new command.
 */
class CommandCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:command';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate a new command class.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Command name.']
    ];

    /**
     * Perform command.
     *
     * @param Reactor $reactor
     */
    public function perform(Reactor $reactor)
    {
        $generator = new CommandGenerator(
            $this->files,
            $this->argument('name'),
            $reactor->config()['generators']['command'],
            $reactor->config()['header']
        );

        if (!$generator->isUnique()) {
            $this->writeln(
                "<fg=red>Class name '{$generator->getClassName()}' is not unique.</fg=red>"
            );

            return;
        }

        $generator->setCommand($this->argument('name'));
        if (!empty($this->option('name'))) {
            $generator->setCommand($this->option('name'));
        }

        if (!empty($this->option('description'))) {
            $generator->setDescription($this->option('description'));
        }

        if (!empty($this->option('comment'))) {
            //User specified comment
            $generator->setComment($this->option('comment'));
        }

        //Generating
        $generator->render();

        $filename = basename($generator->getFilename());
        $this->writeln("<info>Command successfully created:</info> {$filename}");

        //We are have to sleep a little to flush cache
        $this->writeln("Run '<info>console:refresh</info>' to index created command.");
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        return [
            [
                'comment',
                null,
                InputOption::VALUE_OPTIONAL,
                'Optional comment to add as class header.'
            ],
            [
                'name',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Command name.'
            ],
            [
                'description',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Command description.'
            ]
        ];
    }
}
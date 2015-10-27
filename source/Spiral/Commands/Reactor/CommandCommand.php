<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Reactor;

use Spiral\Commands\Reactor\Prototypes\AbstractCommand;
use Spiral\Reactor\Generators\CommandGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create new command.
 */
class CommandCommand extends AbstractCommand
{
    /**
     * Generator class to be used.
     */
    const GENERATOR = CommandGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'command';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:command';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new command.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Command name.']
    ];

    /**
     * Perform command.
     */
    public function perform()
    {
        /**
         * @var CommandGenerator $generator
         */
        if (empty($generator = $this->getGenerator())) {
            return;
        }

        $generator->setCommand($this->argument('name'));
        if (!empty($this->option('name'))) {
            $generator->setCommand($this->option('name'));
        }

        if (!empty($this->option('description'))) {
            $generator->setDescription($this->option('description'));
        }

        //Generating
        $generator->render();

        $filename = basename($generator->getFilename());
        $this->writeln("<info>Command was successfully created:</info> {$filename}");

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
            ],
            [
                'comment',
                null,
                InputOption::VALUE_OPTIONAL,
                'Optional comment to add as class header.'
            ]
        ];
    }
}
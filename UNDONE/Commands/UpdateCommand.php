<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands;

use Spiral\Components\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'update';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Update ORM and ODM schemas and render virtual documentation.';


    /**
     * Command arguments specified in Symphony format. For more complex definitions redefine getArguments()
     * method.
     *
     * @var array
     */
    protected $arguments = [
        ['documenter', InputArgument::OPTIONAL, 'IDE tooltips documenter (for ORM and ODM).'],
    ];

    /**
     * Updating schemas.
     */
    public function perform()
    {
        $this->writeln("Updating ORM and ODM schemas and refreshing IDE tooltip helpers...");
        $this->writeln("");

        $this->console->command('orm:update', $this->input, $this->output);
        $this->console->command('odm:update', $this->input, $this->output);
        $this->writeln("");

        //Inspecting
        $this->console->command('inspect', [
            '--short' => $this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE
        ], $this->output);
    }
}
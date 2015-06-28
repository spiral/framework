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
     * Updating schemas.
     */
    public function perform()
    {
        $this->writeln("Updating ORM and ODM schemas and virtual documentations...");
        $this->writeln("");

        $this->console->command('orm:update', [], $this->output);
        $this->console->command('odm:update', [], $this->output);
        $this->writeln("");

        //Inspecting
        $this->console->command('inspect', [
            '--short' => $this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE
        ], $this->output);
    }
}
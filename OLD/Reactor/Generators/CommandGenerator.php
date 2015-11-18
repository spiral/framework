<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Generators;

use Spiral\Console\Command;
use Spiral\Reactor\Generators\Prototypes\AbstractGenerator;

/**
 * Generate console command.
 */
class CommandGenerator extends AbstractGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function generate()
    {
        $this->file->addUse(Command::class);
        $this->class->setExtends('Command');

        $this->class->property('name', ["@var string"])->setDefault(true, "");
        $this->class->property('description', ["@var string"])->setDefault(true, "");

        $this->class->property('arguments', ["@var string"])->setDefault(true, []);
        $this->class->property('options', ["@var string"])->setDefault(true, []);

        $this->class->method('perform')->setComment("Perform command.");
    }

    /**
     * Set command name.
     *
     * @param string $name
     */
    public function setCommand($name)
    {
        $this->class->property('name')->setDefault(true, $name);
    }

    /**
     * Set command description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->class->property('description')->setDefault(true, $description);
    }
}
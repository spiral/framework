<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Console;

use Spiral\Tokenizer\ClassesInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Locate commands.
 */
class CommandLocator
{
    /**
     * @var ClassesInterface
     */
    protected $classes;

    /**
     * @param ClassesInterface $classes
     */
    public function __construct(ClassesInterface $classes)
    {
        $this->classes = $classes;
    }

    /**
     * Locate all available document schemas in a project.
     *
     * @return array
     */
    public function locateCommands(): array
    {
        $commands = [];
        foreach ($this->classes->getClasses(Command::class) as $class) {
            if ($class['abstract']) {
                continue;
            }

            $commands[] = $class['name'];
        }

        return $commands;
    }
}
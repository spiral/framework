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
class CommandLocator implements LocatorInterface
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
     * {@inheritdoc}
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
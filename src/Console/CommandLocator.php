<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Spiral\Tokenizer\ClassesInterface;

final class CommandLocator implements LocatorInterface
{
    /** @var ClassesInterface */
    private $classes;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ClassesInterface   $classes
     * @param ContainerInterface $container
     */
    public function __construct(ClassesInterface $classes, ContainerInterface $container)
    {
        $this->classes = $classes;
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function locateCommands(): array
    {
        $commands = [];
        foreach ($this->classes->getClasses(\Symfony\Component\Console\Command\Command::class) as $class) {
            if ($class->isAbstract()) {
                continue;
            }

            $commands[] = $this->container->get($class->getName());
        }

        return $commands;
    }
}
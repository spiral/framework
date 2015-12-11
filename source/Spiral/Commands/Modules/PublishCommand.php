<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Modules;

use Spiral\Commands\Modules\Traits\ModuleTrait;
use Spiral\Console\Command;
use Spiral\Core\DirectoriesInterface;
use Spiral\Modules\Entities\Publisher;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Publish all registered modules resources.
 */
class PublishCommand extends Command
{
    use ModuleTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'publish';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Publish specific module resources';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['module', InputArgument::REQUIRED, 'Module class name'],
    ];

    /**
     * @param Publisher            $publisher
     * @param DirectoriesInterface $directories
     */
    public function perform(Publisher $publisher, DirectoriesInterface $directories)
    {
        $class = $this->guessClass($this->argument('module'));
        if (!$this->isModule($class)) {
            $this->writeln("<fg=red>Class '{$class}' is not valid module.</fg=red>");

            return;
        }

        //Publishing
        $this->container->get($class)->publish($publisher, $directories);

        $this->writeln(
            "<info>Module '<comment>{$class}</comment>' has been successfully published.</info>"
        );
    }
}
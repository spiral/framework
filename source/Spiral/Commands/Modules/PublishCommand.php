<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Modules;

use Spiral\Console\Command;
use Spiral\Core\DirectoriesInterface;
use Spiral\Modules\Entities\Publisher;
use Spiral\Modules\ModuleInterface;
use Spiral\Modules\ModuleManager;

/**
 * Publish all registered modules resources.
 */
class PublishCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'modules:publish';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Publish all registered modules resources';

    /**
     * @param ModuleManager        $modules
     * @param Publisher            $publisher
     * @param DirectoriesInterface $directories
     */
    public function perform(
        ModuleManager $modules,
        Publisher $publisher,
        DirectoriesInterface $directories
    ) {
        foreach ($modules->getModules() as $module => $registered) {
            if (!$registered) {
                $this->isVerbosity() && $this->writeln(
                    "Module '<comment>{$module}</comment>' has to be registered first."
                );

                continue;
            }

            $this->isVerbosity() && $this->writeln("Publishing module '<comment>{$module}</comment>'.");

            /**
             * @var ModuleInterface $module
             */
            $module = $this->container->get($module);

            //Publishing
            $module->publish($publisher, $directories);
        }
    }
}
<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Modules;

use Interop\Container\ContainerInterface;
use Spiral\Console\Command;
use Spiral\Modules\Entities\Registrator;
use Spiral\Modules\ModuleInterface;
use Spiral\Modules\ModuleManager;

/**
 * Configure all non-registered modules (alters configuration files).
 */
class ConfigureCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'modules:configure';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Configure all non-registered modules';

    /**
     * @param Registrator   $registrator
     * @param ModuleManager $modules
     */
    public function perform(Registrator $registrator, ModuleManager $modules)
    {
        $newModules = [];
        foreach ($modules->getModules() as $class => $registered) {
            $reflection = new \ReflectionClass($class);
            if (!$reflection->isSubclassOf(ModuleInterface::class)) {
                //Not a module
                continue;
            }

            if ($registered) {
                $this->writeln(
                    "<comment>Module {$reflection->getName()} already registered.</comment>"
                );

                continue;
            }

            $newModules[] = $reflection->getName();

            /**
             * @var ModuleInterface $module
             */
            $module = $this->container->get($class);

            //Altering all requested module configurations
            $module->register($registrator);
        }

        if (!empty($modules)) {
            $this->registerModules($registrator, $newModules);
        }

        //Let's save all updated configs now
        $registrator->save();

        /**
         * Potentially we can remove modules as well.
         */
    }

    /**
     * @param Registrator $registrator
     * @param array       $modules
     */
    protected function registerModules(Registrator $registrator, array $modules)
    {
        foreach ($modules as $module) {
            $registrator->configure('modules', 'modules', $module, ["{$module}::class,"]);
        }
    }
}
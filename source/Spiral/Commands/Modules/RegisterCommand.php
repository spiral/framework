<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Modules;

use Interop\Container\ContainerInterface;
use Spiral\Commands\Modules\Traits\ModuleTrait;
use Spiral\Console\Command;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Modules\Entities\Registrator;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Configure all non-registered modules (alters configuration files).
 */
class RegisterCommand extends Command
{
    use ModuleTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'modules:register';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Register module configs and publish it\'s resources';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['module', InputArgument::REQUIRED, 'Module class name'],
    ];

    /**
     * @param Registrator       $registrator
     * @param ConsoleDispatcher $dispatcher
     */
    public function perform(Registrator $registrator, ConsoleDispatcher $dispatcher)
    {
        $class = $this->guessClass($this->argument('module'));
        if (!$this->isModule($class)) {
            $this->writeln("<fg=red>Class '{$class}' is not valid module.</fg=red>");

            return;
        }

        //Altering all requested module configurations
        $this->container->get($class)->register($registrator);

        //Let's save all updated configs now
        $registrator->save();

        $this->writeln("<info>Module '<comment>{$class}</comment>' has been registered.</info>");

        $dispatcher->command('modules:publish', $this->input, $this->output);
    }
}
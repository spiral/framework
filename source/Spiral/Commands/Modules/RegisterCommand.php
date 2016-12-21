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
use Spiral\Console\ConsoleDispatcher;
use Spiral\Modules\Registrator;
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
    const NAME = 'register';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Register module configs and publish it\'s resources';

    /**
     * {@inheritdoc}
     */
    const ARGUMENTS = [
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

        /**
         * Sometimes modules request to alter some config files, we need user confirmation for that.
         */
        if (!empty($registrator->getInjected())) {
            $table = $this->table(['Config', 'Section', 'Added Lines']);
            foreach ($registrator->getInjected() as $injected) {
                $table->addRow([
                    "<info>{$injected['config']}</info>",
                    "{$injected['placeholder']}",
                    join("\n", $injected['lines'])
                ]);
            }

            $this->writeln("<comment>Following configs are being altered:</comment>");
            $table->render();

            $this->writeln("");
            if (!$this->ask()->confirm("Confirm module registration (y/n)")) {
                return;
            }

            $this->writeln("");
        }

        //Let's save all updated configs now
        $registrator->save();

        $this->writeln(
            "<info>Module '<comment>{$class}</comment>' has been successfully registered.</info>"
        );

        $dispatcher->command('publish', $this->input, $this->output);
    }
}
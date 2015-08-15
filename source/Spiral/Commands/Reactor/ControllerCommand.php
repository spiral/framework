<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor;

use Spiral\Console\Command;
use Spiral\Reactor\Generators\ControllerGenerator;
use Spiral\Reactor\Reactor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate controller class.
 */
class ControllerCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:controller';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate a new controller class.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Controller name.']
    ];

    /**
     * Perform command.
     *
     * @param Reactor $reactor
     */
    public function perform(Reactor $reactor)
    {
        $generator = new ControllerGenerator(
            $this->files,
            $this->argument('name'),
            $reactor->config()['generators']['controller'],
            $reactor->config()['header']
        );

        if (!$generator->isUnique()) {
            $this->writeln(
                "<fg=red>Class name '{$generator->getClassName()}' is not unique.</fg=red>"
            );

            return;
        }

        foreach ($this->option('method') as $method) {
            $generator->addMethod($method);
        }

        foreach ($this->option('depends') as $service) {
            if (empty($class = $reactor->findClass('service', $service))) {
                $this->writeln(
                    "<fg=red>Unable to locate service class for '{$service}'.</fg=red>"
                );

                return;
            }

            $generator->addDependency($service, $class);
        }

        if (!empty($this->option('comment'))) {
            //User specified comment
            $generator->setComment($this->option('comment'));
        }

        //Generating
        $generator->render();

        $filename = basename($generator->getFilename());
        $this->writeln("<info>Migration successfully created:</info> {$filename}");
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        return [
            [
                'method',
                'm',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Pre-create controller method.'
            ],
            [
                'depends',
                'd',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Add service dependency to controller. Declare dependency in short form.'
            ],
            [
                'comment',
                null,
                InputOption::VALUE_OPTIONAL,
                'Optional comment to add as class header.'
            ]
        ];
    }
}
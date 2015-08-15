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
use Spiral\Reactor\Generators\ServiceGenerator;
use Spiral\Reactor\Reactor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate service with it's dependecies and associated model.
 */
class ServiceCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:service';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate a new controller class.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Service name.'],
        ['entity', InputArgument::OPTIONAL, 'Name of associated entity (ORM or ODM) in short form.']
    ];

    /**
     * Perform command.
     *
     * @param Reactor $reactor
     */
    public function perform(Reactor $reactor)
    {
        $generator = new ServiceGenerator(
            $this->files,
            $this->argument('name'),
            $reactor->config()['generators']['service'],
            $reactor->config()['header']
        );

        if (!$generator->isUnique()) {
            $this->writeln(
                "<fg=red>Class name '{$generator->getClassName()}' is not unique.</fg=red>"
            );

            return;
        }

        if (!empty($model = $this->argument('entity'))) {
            if (empty($class = $reactor->findClass('entity', $model))) {
                $this->writeln(
                    "<fg=red>Unable to locate model class for '{$model}'.</fg=red>"
                );

                return;
            }

            $generator->associateModel($model, $class);
        }

        if ($this->option('singleton')) {
            $generator->makeSingleton();
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
        $this->writeln("<info>Service successfully created:</info> {$filename}");
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
                'singleton',
                's',
                InputOption::VALUE_NONE,
                'Mark service as singleton.'
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
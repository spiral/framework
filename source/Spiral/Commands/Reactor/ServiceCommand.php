<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Reactor;

use Spiral\Commands\Reactor\Prototypes\AbstractCommand;
use Spiral\Reactor\Generators\ServiceGenerator;
use Spiral\Reactor\Reactor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate service with it's dependecies and associated model.
 */
class ServiceCommand extends AbstractCommand
{
    /**
     * Generator class to be used.
     */
    const GENERATOR = ServiceGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'service';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:service';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new service.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Service name.']
    ];

    /**
     * Perform command.
     *
     * @param Reactor $reactor
     */
    public function perform(Reactor $reactor)
    {
        /**
         * @var ServiceGenerator $generator
         */
        if (empty($generator = $this->getGenerator())) {
            return;
        }

        foreach ($this->option('method') as $method) {
            $generator->addMethod($method);
        }

        if (!empty($model = $this->option('entity'))) {
            if (empty($class = $reactor->findClass('entity', $model))) {
                $this->writeln(
                    "<fg=red>Unable to locate model class for '{$model}'.</fg=red>"
                );

                return;
            }

            $generator->associateModel($model, $class);
        }

        if (!$this->option('mortal')) {
            $generator->makeSingleton();
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

        //Generating
        $generator->render();

        $filename = basename($generator->getFilename());
        $this->writeln("<info>Service was successfully created:</info> {$filename}");
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
                'Pre-create service method.'
            ],
            [
                'entity',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Name of associated entity (ORM or ODM) in short form.'
            ],
            [
                'depends',
                'd',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Add service dependency. Declare dependency in short form.'
            ],
            [
                'mortal',
                's',
                InputOption::VALUE_NONE,
                'Do not make service instance SingletonInterface.'
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
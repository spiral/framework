<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Reactor;

use Spiral\Commands\Reactor\Prototypes\AbstractCommand;
use Spiral\Reactor\Generators\ControllerGenerator;
use Spiral\Reactor\Reactor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate controller class.
 */
class ControllerCommand extends AbstractCommand
{
    /**
     * Generator class to be used.
     */
    const GENERATOR = ControllerGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'controller';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:controller';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new controller.';

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
        /**
         * @var ControllerGenerator $generator
         */
        if (empty($generator = $this->getGenerator())) {
            return;
        }

        foreach ($this->option('action') as $action) {
            $generator->addAction($action);
        }

        foreach ($this->option('method') as $method) {
            $generator->addMethod($method);
        }

        if (!empty($service = $this->option('service'))) {
            if (empty($serviceClass = $reactor->findClass('service', $service))) {
                $this->writeln(
                    "<fg=red>Unable to locate service class for '{$service}'.</fg=red>"
                );

                return;
            }

            //Pre-generate methods using data entity service
            if (!empty($request = $this->option('request'))) {
                if (empty($requestClass = $reactor->findClass('request', $request))) {
                    $this->writeln(
                        "<fg=red>Unable to locate request class for '{$request}'.</fg=red>"
                    );

                    return;
                }

                //Use request instead of POST data to create and update data entity
                $generator->createCRUD($service, $serviceClass, $request, $requestClass);
            } else {
                $generator->createCRUD($service, $serviceClass);
            }
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
        $this->writeln("<info>Controller has been successfully created:</info> {$filename}");
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        return [
            [
                'action',
                'a',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Pre-create controller action.'
            ],
            [
                'method',
                'm',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Pre-create controller method.'
            ],
            [
                'service',
                's',
                InputOption::VALUE_OPTIONAL,
                'Generate set of CRUD operations for given entity service.'
            ],
            [
                'request',
                'r',
                InputOption::VALUE_OPTIONAL,
                'RequestFilter to be used for update/create operations.'
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
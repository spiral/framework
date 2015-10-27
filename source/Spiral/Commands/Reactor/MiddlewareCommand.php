<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Commands\Reactor;

use Spiral\Commands\Reactor\Prototypes\AbstractCommand;
use Spiral\Reactor\Generators\MiddlewareGenerator;
use Spiral\Reactor\Reactor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Generate empty http middleware.
 */
class MiddlewareCommand extends AbstractCommand
{
    /**
     * Generator class to be used.
     */
    const GENERATOR = MiddlewareGenerator::class;

    /**
     * Generation type to be used.
     */
    const TYPE = 'middleware';

    /**
     * {@inheritdoc}
     */
    protected $name = 'create:middleware';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate new http middleware.';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Middleware name.'],
    ];

    /**
     * Perform command.
     *
     * @param Reactor $reactor
     */
    public function perform(Reactor $reactor)
    {
        /**
         * @var MiddlewareGenerator $generator
         */
        if (empty($generator = $this->getGenerator())) {
            return;
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
        $this->writeln("<info>Http middleware was successfully created:</info> {$filename}");
        $this->writeln("You register your middleware by assigning it to Route or via http config.");
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        return [
            [
                'depends',
                'd',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Add service dependency to middleware. Declare dependency in short form.'
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
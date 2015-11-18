<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)

 */
namespace Spiral\Commands\Reactor\Prototypes;

use Spiral\Console\Command;
use Spiral\Reactor\Generators\Prototypes\AbstractGenerator;
use Spiral\Reactor\Reactor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Abstract command.
 */
class AbstractCommand extends Command
{
    /**
     * Generator class to be used.
     */
    const GENERATOR = null;

    /**
     * Generation type to be used.
     */
    const TYPE = '';

    /**
     * {@inheritdoc}
     */
    protected $arguments = [
        ['name', InputArgument::REQUIRED, 'Class name in short form.']
    ];

    /**
     * Get instance of generator.
     *
     * @return AbstractGenerator|null
     */
    protected function getGenerator()
    {
        $generator = static::GENERATOR;
        $reactor = $this->container->get(Reactor::class);

        /**
         * @var AbstractGenerator $generator
         */
        $generator = new $generator(
            $this->files,
            $this->argument('name'),
            $reactor->config()['generators'][static::TYPE],
            $reactor->config()['header']
        );

        if (!$generator->isUnique()) {
            $this->writeln(
                "<fg=red>Class name '{$generator->getClassName()}' is not unique.</fg=red>"
            );

            return null;
        }

        if (!empty($this->option('comment'))) {
            //User specified comment
            $generator->setComment($this->option('comment'));
        }

        return $generator;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        return [
            [
                'comment',
                null,
                InputOption::VALUE_OPTIONAL,
                'Optional comment to add as class header.'
            ]
        ];
    }

    /**
     * In some cases generators might want to include rendered files, to prevent console from
     * skipping such commands (due they have includes) we moving operation on higher level.
     *
     * @param string $filename
     */
    protected function includeFile($filename)
    {
        include_once($filename);
    }
}
<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Commands\Views;

use Spiral\Commands\Views\Helpers\ViewLocator;
use Spiral\Console\Command;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Views\ViewManager;
use Symfony\Component\Console\Helper\FormatterHelper;

/**
 * Compile every available view file and store result in view cache.
 */
class CompileCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    const NAME = 'views:compile';

    /**
     * {@inheritdoc}
     */
    const DESCRIPTION = 'Compile every available view file';

    /**
     * @param ViewLocator       $locator
     * @param ViewManager       $manager
     * @param ConsoleDispatcher $dispatcher
     */
    public function perform(
        ViewLocator $locator,
        ViewManager $manager,
        ConsoleDispatcher $dispatcher
    ) {
        //To clean up cache
        $dispatcher->command('views:reset', [], $this->output);

        /**
         * @var FormatterHelper $formatter
         */
        $formatter = $this->getHelper('formatter');
        foreach ($locator->getNamespaces() as $namespace) {
            $this->isVerbosity() && $this->writeln(
                "\n<info>Compiling views in namespace '<comment>{$namespace}</comment>'.</info>"
            );

            foreach ($locator->getViews($namespace) as $view => $engine) {
                if ($this->isVerbosity()) {
                    $this->write($formatter->formatSection("{$engine}", $view . ", ", 'fg=cyan'));
                }

                $start = microtime(true);
                try {
                    //Compilation
                    $manager->engine($engine)->compile("{$namespace}:{$view}", true);

                    $this->isVerbosity() && $this->write("<info>ok</info>");
                } catch (\Exception $exception) {
                    if ($this->isVerbosity()) {
                        $this->write("<fg=red>error: {$exception->getMessage()}</fg=red>");
                    }
                } finally {
                    $elapsed = number_format((microtime(true) - $start) * 1000);
                    if ($this->isVerbosity()) {
                        $this->writeln(" <comment>[{$elapsed} ms]</comment> ");
                    }
                }
            }
        }

        $this->writeln("<info>View cache was successfully generated.</info>");
    }
}
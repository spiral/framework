<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Commands\Views;

use Spiral\Console\Command;
use Symfony\Component\Console\Helper\FormatterHelper;

class CompileCommand extends Command
{
    /**
     * Command name.
     *
     * @var string
     */
    protected $name = 'views:compile';

    /**
     * Short command description.
     *
     * @var string
     */
    protected $description = 'Compile every available view file.';

    /**
     * Compile available view files.
     */
    public function perform()
    {
        /**
         * @var FormatterHelper $formatter
         */
        $formatter = $this->getHelper('formatter');

        foreach ($this->views->getNamespaces() as $namespace => $directories)
        {
            if (empty($views = $this->views->getViews($namespace)))
            {
                continue;
            }

            $this->isVerbose() && $this->writeln(
                "Compiling views in namespace '<comment>{$namespace}</comment>'."
            );

            foreach ($views as $view => $engine)
            {
                $this->isVerbose() && $this->write($formatter->formatSection(
                    $namespace, $view . ", ", 'fg=cyan'
                ));

                $start = microtime(true);
                $this->views->getFilename($namespace, $view, true, true);
                $elapsed = number_format((microtime(true) - $start) * 1000);

                $this->isVerbose() && $this->writeln("<comment>{$elapsed}</comment> ms");
            }
        }

        $this->writeln("<info>View cache successfully generated.</info>");
    }
}
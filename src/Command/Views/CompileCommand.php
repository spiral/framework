<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Command\Views;

use Codedungeon\PHPCliColors\Color;
use Spiral\Console\Command;
use Spiral\Views\ContextGenerator;
use Spiral\Views\ContextInterface;
use Spiral\Views\EngineInterface;
use Spiral\Views\ViewManager;

/**
 * Warm up view cache.
 */
class CompileCommand extends Command
{
    const NAME        = 'views:compile';
    const DESCRIPTION = 'Warm-up view cache';

    /**
     * @param ViewManager $views
     */
    public function perform(ViewManager $views)
    {
        $generator = new ContextGenerator($views->getContext());

        foreach ($generator->generate() as $context) {
            $this->sprintf("<info>Context</info> {%s}\n", $this->packContext($context));

            foreach ($views->getEngines() as $engine) {
                $this->compile($engine, $context);
            }
        }

        $this->writeln("View cache has been generated.");
    }

    protected function packContext(ContextInterface $context): string
    {
        $values = [];

        foreach ($context->getDependencies() as $dependency) {
            $values[] = sprintf(
                "%s%s%s:%s%s%s",
                Color::LIGHT_WHITE,
                $dependency->getName(),
                Color::RESET,
                Color::LIGHT_CYAN,
                $dependency->getValue(),
                Color::RESET
            );
        }

        return join(', ', $values);
    }

    /**
     * @param EngineInterface  $engine
     * @param ContextInterface $context
     */
    public function compile(EngineInterface $engine, ContextInterface $context)
    {
        $name = (new \ReflectionObject($engine))->getShortName();

        foreach ($engine->getLoader()->list() as $path) {
            if ($this->isVerbose()) {
                $this->sprintf("<fg=yellow>[%s]</fg=yellow> %s, ", $name, $path);
            }

            try {
                $start = microtime(true);
                $engine->compile($path, $context);
                $this->isVerbose() && $this->write("<info>ok</info>");
            } catch (\Throwable $e) {
                if ($this->isVerbose()) {
                    $this->sprintf("<fg=red>error: %s</fg=red>", $e->getMessage());
                }
                continue;
            } finally {
                if ($this->isVerbose()) {
                    $this->sprintf(
                        " %s[%s ms]%s\n",
                        Color::GRAY,
                        number_format((microtime(true) - $start) * 1000),
                        Color::RESET
                    );
                }
            }
        }
    }
}
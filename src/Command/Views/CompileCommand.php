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
    private const MIN_WIDTH = 8;

    const NAME        = 'views:compile';
    const DESCRIPTION = 'Warm-up view cache';

    /**
     * @param ViewManager $views
     */
    public function perform(ViewManager $views)
    {
        $generator = new ContextGenerator($views->getContext());

        $contexts = $generator->generate();
        if (empty($contexts)) {
            $contexts[] = $views->getContext();
        }

        foreach ($contexts as $context) {
            foreach ($views->getEngines() as $engine) {
                $this->compile($engine, $context);
            }
        }

        $this->writeln("View cache has been generated.");
    }

    /**
     * @param EngineInterface  $engine
     * @param ContextInterface $context
     */
    protected function compile(EngineInterface $engine, ContextInterface $context)
    {
        $this->sprintf(
            "<fg=yellow>%s</fg=yellow> [%s]\n",
            $this->describeEngine($engine),
            $this->describeContext($context)
        );

        foreach ($engine->getLoader()->list() as $path) {
            try {
                $start = microtime(true);
                $engine->reset($path, $context);
                $engine->compile($path, $context);

                if ($this->isVerbose()) {
                    $this->sprintf("<info>•</info> %s", $path);
                }
            } catch (\Throwable $e) {
                if ($this->isVerbose()) {
                    $this->sprintf(
                        "<fg=red>•</fg=red> %s: <fg=red>%s at line %s</fg=red>",
                        $path,
                        $e->getMessage(),
                        $e->getLine()
                    );
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

        if ($this->isVerbose()) {
            if (empty($path)) {
                $this->writeln("• no views found");
            }

            $this->write("\n");
        }
    }

    /**
     * @param ContextInterface $context
     * @return string
     */
    private function describeContext(ContextInterface $context): string
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

    private function describeEngine(EngineInterface $engine): string
    {
        $refection = new \ReflectionObject($engine);

        return $refection->getShortName();
    }
}

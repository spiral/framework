<?php

declare(strict_types=1);

namespace Spiral\Command\Views;

use Codedungeon\PHPCliColors\Color;
use Spiral\Console\Command;
use Spiral\Views\ContextGenerator;
use Spiral\Views\ContextInterface;
use Spiral\Views\Engine\Native\NativeEngine;
use Spiral\Views\EngineInterface;
use Spiral\Views\ViewManager;

/**
 * Warm up view cache.
 */
final class CompileCommand extends Command
{
    protected const NAME        = 'views:compile';
    protected const DESCRIPTION = 'Warm-up view cache';

    public function perform(ViewManager $views): int
    {
        $generator = new ContextGenerator($views->getContext());

        $contexts = $generator->generate();
        if (empty($contexts)) {
            $contexts[] = $views->getContext();
        }

        foreach ($contexts as $context) {
            foreach ($views->getEngines() as $engine) {
                if ($engine instanceof NativeEngine) {
                    // no need to compile
                    continue;
                }

                $this->compile($engine, $context);
            }
        }

        $this->writeln('View cache has been generated.');

        return self::SUCCESS;
    }

    protected function compile(EngineInterface $engine, ContextInterface $context): void
    {
        $this->sprintf(
            "<fg=yellow>%s</fg=yellow> [%s]\n",
            $this->describeEngine($engine),
            $this->describeContext($context) ?? 'default'
        );

        foreach ($engine->getLoader()->list() as $path) {
            $start = \microtime(true);
            try {
                $engine->reset($path, $context);
                $engine->compile($path, $context);

                if ($this->isVerbose()) {
                    $this->sprintf('<info>•</info> %s', $path);
                }
            } catch (\Throwable $e) {
                $this->renderError($path, $e);
                continue;
            } finally {
                $this->renderElapsed($start);
            }
        }

        $this->renderSuccess($path ?? null);
    }

    protected function renderError(string $path, \Throwable $e): void
    {
        if (!$this->isVerbose()) {
            return;
        }

        $this->sprintf(
            '<fg=red>•</fg=red> %s: <fg=red>%s at line %s</fg=red>',
            $path,
            $e->getMessage(),
            $e->getLine()
        );
    }

    private function describeContext(ContextInterface $context): ?string
    {
        $values = [];

        foreach ($context->getDependencies() as $dependency) {
            $values[] = \sprintf(
                '%s%s%s:%s%s%s',
                Color::LIGHT_WHITE,
                $dependency->getName(),
                Color::RESET,
                Color::LIGHT_CYAN,
                $dependency->getValue(),
                Color::RESET
            );
        }

        return $values === [] ? null : \implode(', ', $values);
    }

    private function describeEngine(EngineInterface $engine): string
    {
        return (new \ReflectionObject($engine))->getShortName();
    }

    private function renderSuccess(string $lastPath = null): void
    {
        if (!$this->isVerbose()) {
            return;
        }

        if ($lastPath === null) {
            $this->writeln('• no views found');
        }

        $this->write("\n");
    }

    private function renderElapsed(float $start): void
    {
        if (!$this->isVerbose()) {
            return;
        }

        $this->sprintf(
            " %s[%s ms]%s\n",
            Color::GRAY,
            \number_format((\microtime(true) - $start) * 1000),
            Color::RESET
        );
    }
}

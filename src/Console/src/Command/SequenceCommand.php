<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console\Command;

use Psr\Container\ContainerInterface;
use Spiral\Console\Command;
use Spiral\Console\SequenceInterface;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

abstract class SequenceCommand extends Command
{
    public const    OPTIONS = [
        ['ignore', 'i', InputOption::VALUE_NONE, 'Ignore any errors'],
        ['break', 'b', InputOption::VALUE_NONE, 'Break on first error, works if ignore is disabled'],
    ];

    /**
     * @param iterable|SequenceInterface[] $commands
     * @param ContainerInterface           $container
     */
    protected function runSequence(iterable $commands, ContainerInterface $container): int
    {
        $errors = 0;
        foreach ($commands as $sequence) {
            $sequence->writeHeader($this->output);

            try {
                $sequence->execute($container, $this->output);
                $sequence->whiteFooter($this->output);
            } catch (Throwable $e) {
                $errors++;
                $this->sprintf("<error>%s</error>\n", $e);
                if (!$this->option('ignore') && $this->option('break')) {
                    $this->writeln('<fg=red>Aborting.</fg=red>');

                    return 1;
                }
            }

            $this->writeln('');
        }

        $this->writeln('<info>All done!</info>');

        return ($errors && !$this->option('ignore')) ? 1 : 0;
    }
}

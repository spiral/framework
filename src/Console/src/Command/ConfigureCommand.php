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
use Spiral\Console\Config\ConsoleConfig;

final class ConfigureCommand extends SequenceCommand
{
    protected const NAME        = 'configure';
    protected const DESCRIPTION = 'Configure project';

    public function perform(ConsoleConfig $config, ContainerInterface $container): int
    {
        $this->writeln("<info>Configuring project:</info>\n");

        return $this->runSequence($config->configureSequence(), $container);
    }
}

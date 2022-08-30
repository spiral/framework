<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Batches multiple commands together.
 */
interface SequenceInterface
{
    public function writeHeader(OutputInterface $output);

    /**
     * Execute sequence command or function.
     *
     *
     * @throws \Exception
     */
    public function execute(ContainerInterface $container, OutputInterface $output);

    public function whiteFooter(OutputInterface $output);
}

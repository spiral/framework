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
    /**
     * @param OutputInterface $output
     */
    public function writeHeader(OutputInterface $output);

    /**
     * Execute sequence command or function.
     *
     * @param ContainerInterface $container
     * @param OutputInterface    $output
     *
     * @throws \Exception
     */
    public function execute(ContainerInterface $container, OutputInterface $output);

    /**
     * @param OutputInterface $output
     */
    public function whiteFooter(OutputInterface $output);
}

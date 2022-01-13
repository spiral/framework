<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Console\Sequence;

use Spiral\Console\SequenceInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractSequence implements SequenceInterface
{
    /** @var string */
    private $header;

    /** @var string */
    private $footer;

    public function __construct(string $header, string $footer)
    {
        $this->header = $header;
        $this->footer = $footer;
    }

    /**
     * @inheritdoc
     */
    public function writeHeader(OutputInterface $output): void
    {
        if (!empty($this->header)) {
            $output->writeln($this->header);
        }
    }

    /**
     * @inheritdoc
     */
    public function whiteFooter(OutputInterface $output): void
    {
        if (!empty($this->footer)) {
            $output->writeln($this->footer);
        }
    }
}

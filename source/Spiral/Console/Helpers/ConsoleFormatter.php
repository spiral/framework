<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Console\Helpers;

use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Simple console logger/formatter. Provides ability to disable display only specified set of log
 * messages and use formatting section to prefix every line. This is not real console logger.
 */
class ConsoleFormatter extends AbstractLogger
{
    /**
     * LogLevels associated with their formats.
     *
     * @var array
     */
    private $formats = [];

    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * When section is specified ConsoleFormatter will prefix every line with section name using
     * FormatterHelper.
     *
     * @var string
     */
    protected $section = '';

    /**
     * @var FormatterHelper
     */
    protected $formatter = null;

    /**
     * @param OutputInterface $output
     * @param array           $formats
     * @param string          $section Formatting section, optional.
     */
    public function __construct(OutputInterface $output, array $formats, $section = '')
    {
        $this->output = $output;
        $this->formats = $formats;

        if (!empty($this->section = $section)) {
            //We requested to show messages under one section
            $this->formatter = new FormatterHelper();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if (!array_key_exists($level, $this->formats)) {
            return;
        }

        $string = \Spiral\interpolate($message, $context);
        if (!empty($this->formats[$level])) {
            //Formatting string
            $string = \Spiral\interpolate('<{format}>{string}</{format}>', [
                'string' => $string,
                'format' => $this->formats[$level]
            ]);
        }

        if (!empty($this->section) && !empty($this->formatter)) {
            $string = $this->formatter->formatSection($this->section, $string);
        }

        $this->output->writeln($string);
    }
}
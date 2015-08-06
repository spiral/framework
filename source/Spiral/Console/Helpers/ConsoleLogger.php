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
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends AbstractLogger
{
    private $levels = [

    ];

    private $formats = [];

    protected $output = null;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setLevel($level, $enabled)
    {
        $this->levels[$level] = $enabled;
    }

    public function setFormat($level, $format)
    {
        $this->formats[$level] = $format;
    }

    public function getFormat($level)
    {
        return $this->formats[$level];
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->levels[$level])
        {
            $string = \Spiral\interpolate($message, $context);

            //Formatting
            if (!empty($this->formats[$level]))
            {
                $string = \Spiral\interpolate('<{format}>{string}</{format}', [
                    'string' => $string,
                    'format' => $this->formats[$level]
                ]);
            }

            $this->output->writeln($string);
        }
    }
}
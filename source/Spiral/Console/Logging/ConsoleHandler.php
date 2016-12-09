<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Console\Logging;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides ability to output log messages in console in realtime.
 */
class ConsoleHandler extends AbstractHandler
{
    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * @var array
     */
    protected $styles = [
        'DEBUG'     => 'fg=green',
        'INFO'      => 'fg=cyan',
        'NOTICE'    => 'fg=yellow',
        'WARNING'   => 'fg=yellow',
        'ERROR'     => 'fg=red',
        'CRITICAL'  => 'fg=red',
        'ALERT'     => 'fg=red',
        'EMERGENCY' => 'fg=red',
    ];

    /**
     * @param OutputInterface $output
     * @param bool|int        $level
     * @param bool|true       $bubble
     */
    public function __construct(
        OutputInterface $output,
        $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->output = $output;
    }

    /**
     * @param array $record
     *
     * @return bool
     */
    public function handle(array $record): bool
    {
        if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE) {
            //Showing log
            $this->output->writeln($this->formatMessage(
                $record['channel'],
                $record['level_name'],
                $record['message'],
                $record['context']
            ));

            return true;
        }

        return false;
    }

    /**
     * @param string $channel
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    protected function formatMessage($channel, $level, $message, array $context): string
    {
        $message = \Spiral\interpolate($message, $context);

        $reflection = new \ReflectionClass($channel);
        $channel = $reflection->getShortName();

        /**
         * We are going to format message our own style.
         */
        $this->output->writeln(\Spiral\interpolate(
            "<{style}>{prefix}</{style}> {message}",
            [
                'style'   => $this->prefixStyle($level),
                'prefix'  => $this->getPrefix($channel),
                'message' => $message
            ]
        ));

        //Nothing to echo
        return '';
    }

    /**
     * @param string $level
     *
     * @return string
     */
    protected function prefixStyle(string $level): string
    {
        return $this->styles[$level];
    }

    /**
     * @param string $channel
     *
     * @return string
     */
    private function getPrefix(string $channel): string
    {
        if (!class_exists($channel, false)) {
            return "[{$channel}]";
        }

        $reflection = new \ReflectionClass($channel);

        return "[{$reflection->getShortName()}]";
    }
}
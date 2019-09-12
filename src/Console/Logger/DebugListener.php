<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Console\Logger;

use Codedungeon\PHPCliColors\Color;
use Psr\Log\LogLevel;
use Spiral\Logger\Event\LogEvent;
use Spiral\Logger\LogsInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DebugListener
{
    protected const STYLES = [
        LogLevel::DEBUG     => 'fg=green',
        LogLevel::INFO      => 'fg=cyan',
        LogLevel::NOTICE    => 'fg=yellow',
        LogLevel::WARNING   => 'fg=yellow',
        LogLevel::ERROR     => 'fg=red',
        LogLevel::CRITICAL  => 'fg=red',
        LogLevel::ALERT     => 'fg=red',
        LogLevel::EMERGENCY => 'fg=red',
    ];

    /** @var LogsInterface|null */
    private $logs;

    /** @var OutputInterface|null */
    private $output;

    /**
     * @param LogsInterface|null $logs
     */
    public function __construct(LogsInterface $logs = null)
    {
        $this->logs = $logs;
    }

    /**
     * Handle and display log event.
     *
     * @param LogEvent $event
     */
    public function __invoke(LogEvent $event)
    {
        if (empty($this->output)) {
            return;
        }

        if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERY_VERBOSE) {
            return;
        }

        /**
         * We are going to format message our own style.
         */
        $this->output->writeln(sprintf(
            '<%1$s>%2$s</%1$s> %3$s',
            $this->getStyle($event->getLevel()),
            $this->getChannel($event->getChannel()),
            $this->getMessage($this->output->isDecorated(), $event->getMessage())
        ));
    }

    /**
     * Configure listener with new output.
     *
     * @param OutputInterface $output
     * @return DebugListener
     */
    public function withOutput(OutputInterface $output): self
    {
        $listener = clone $this;
        $listener->output = $output;

        return $listener;
    }

    /**
     * Enable logging in console mode.
     *
     * @return DebugListener
     */
    public function enable(): self
    {
        if (!empty($this->logs)) {
            $this->logs->addListener($this);
        }

        return $this;
    }

    /**
     * Disable displaying logs in console.
     *
     * @return DebugListener
     */
    public function disable(): self
    {
        if (!empty($this->logs)) {
            $this->logs->removeListener($this);
        }

        return $this;
    }

    /**
     * @param string $level
     *
     * @return string
     */
    protected function getStyle(string $level): string
    {
        return self::STYLES[$level];
    }

    /**
     * @param string $channel
     * @return string
     */
    private function getChannel(string $channel): string
    {
        if (!class_exists($channel, false)) {
            return "[{$channel}]";
        }

        try {
            $reflection = new \ReflectionClass($channel);
        } catch (\ReflectionException $e) {
            return $channel;
        }

        return "[{$reflection->getShortName()}]";
    }

    /**
     * @param bool   $decorated
     * @param string $message
     * @return string
     */
    private function getMessage(bool $decorated, string $message)
    {
        if (!$decorated) {
            return $message;
        }

        return Color::GRAY . $message . Color::RESET;
    }
}

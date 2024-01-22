<?php

declare(strict_types=1);

namespace Spiral\Console\Logger;

use Codedungeon\PHPCliColors\Color;
use Psr\Log\LogLevel;
use Spiral\Logger\Event\LogEvent;
use Spiral\Logger\ListenerRegistryInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DebugListener
{
    private const STYLES = [
        LogLevel::DEBUG     => 'fg=green',
        LogLevel::INFO      => 'fg=cyan',
        LogLevel::NOTICE    => 'fg=yellow',
        LogLevel::WARNING   => 'fg=yellow',
        LogLevel::ERROR     => 'fg=red',
        LogLevel::CRITICAL  => 'fg=red',
        LogLevel::ALERT     => 'fg=red',
        LogLevel::EMERGENCY => 'fg=red',
    ];

    private ?OutputInterface $output = null;

    public function __construct(
        private readonly ListenerRegistryInterface $listenerRegistry
    ) {
    }

    /**
     * Handle and display log event.
     */
    public function __invoke(LogEvent $event): void
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
        $this->output->writeln(
            \sprintf(
                '<%1$s>%2$s</%1$s> %3$s',
                $this->getStyle($event->getLevel()),
                $this->getChannel($event->getChannel()),
                $this->getMessage($this->output->isDecorated(), $event->getMessage())
            )
        );
    }

    /**
     * Configure listener with new output.
     */
    public function withOutput(OutputInterface $output): self
    {
        $listener = clone $this;
        $listener->output = $output;

        return $listener;
    }

    /**
     * Enable logging in console mode.
     * @psalm-suppress InvalidReturnType, InvalidReturnStatement
     */
    public function enable(): self
    {
        $this->listenerRegistry->addListener($this);

        return $this;
    }

    /**
     * Disable displaying logs in console.
     * @psalm-suppress InvalidReturnType, InvalidReturnStatement
     */
    public function disable(): self
    {
        $this->listenerRegistry->removeListener($this);

        return $this;
    }

    protected function getStyle(string $level): string
    {
        return self::STYLES[$level];
    }

    private function getChannel(string $channel): string
    {
        if (!\class_exists($channel, false)) {
            return \sprintf('[%s]', $channel);
        }

        try {
            $reflection = new \ReflectionClass($channel);
        } catch (\ReflectionException) {
            return $channel;
        }

        return \sprintf('[%s]', $reflection->getShortName());
    }

    private function getMessage(bool $decorated, string $message): string
    {
        if (!$decorated) {
            return $message;
        }

        return Color::GRAY . $message . Color::RESET;
    }
}

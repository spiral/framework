<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Driver;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class LogBroadcast extends AbstractBroadcast
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function authorize(ServerRequestInterface $request): bool
    {
        return true;
    }

    public function publish($topics, $messages): void
    {
        $topics = implode(', ', $this->formatTopics($this->toArray($topics)));

        /** @var string $message */
        foreach ($this->toArray($messages) as $message) {
            assert(\is_string($message), 'Message argument must be a type of string');
            $this->logger->info('Broadcasting on channels [' . $topics . '] with payload: ' . $message);
        }
    }
}

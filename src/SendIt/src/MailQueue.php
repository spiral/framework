<?php

declare(strict_types=1);

namespace Spiral\SendIt;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Mailer\Event\MessageSent;
use Spiral\Mailer\MailerInterface;
use Spiral\Mailer\MessageInterface;
use Spiral\Queue\Options;
use Spiral\Queue\QueueInterface;
use Spiral\SendIt\Config\MailerConfig;

final class MailQueue implements MailerInterface
{
    public const JOB_NAME = 'sendit.mail';

    public function __construct(
        private readonly MailerConfig $config,
        private readonly QueueInterface $queue,
        private readonly ?EventDispatcherInterface $eventDispatcher = null
    ) {
    }

    public function send(MessageInterface ...$message): void
    {
        $options = Options::onQueue($this->config->getQueue());

        foreach ($message as $msg) {
            $this->queue->push(
                self::JOB_NAME,
                MessageSerializer::pack($msg),
                $options->withDelay($msg->getOptions()['delay'] ?? null)
            );

            $this->eventDispatcher?->dispatch(new MessageSent($msg));
        }
    }
}

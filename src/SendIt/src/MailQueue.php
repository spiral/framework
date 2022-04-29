<?php

declare(strict_types=1);

namespace Spiral\SendIt;

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
        private readonly QueueInterface $queue
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
        }
    }
}

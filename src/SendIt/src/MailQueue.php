<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt;

use Spiral\Mailer\MailerInterface;
use Spiral\Mailer\MessageInterface;
use Spiral\Queue\Options;
use Spiral\SendIt\Config\MailerConfig;

final class MailQueue implements MailerInterface
{
    public const JOB_NAME = 'sendit.mail';

    /** @var MailerConfig */
    private $config;

    /** @var \Spiral\Queue\QueueInterface */
    private $queue;

    public function __construct(MailerConfig $config, $queue)
    {
        $this->config = $config;
        $this->queue = $queue;
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

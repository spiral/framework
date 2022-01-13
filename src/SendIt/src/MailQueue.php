<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt;

use Spiral\Jobs\Options;
use Spiral\Jobs\QueueInterface;
use Spiral\Mailer\MailerInterface;
use Spiral\Mailer\MessageInterface;
use Spiral\SendIt\Config\MailerConfig;

final class MailQueue implements MailerInterface
{
    public const JOB_NAME = 'sendit.mail';

    /** @var MailerConfig */
    private $config;

    /** @var QueueInterface */
    private $queue;

    public function __construct(MailerConfig $config, QueueInterface $queue)
    {
        $this->config = $config;
        $this->queue = $queue;
    }

    public function send(MessageInterface ...$message): void
    {
        foreach ($message as $msg) {
            $this->queue->push(
                self::JOB_NAME,
                MessageSerializer::pack($msg),
                (new Options())->withPipeline($this->config->getQueuePipeline())
            );
        }
    }
}

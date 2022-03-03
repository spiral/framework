<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt;

use Spiral\Jobs\Options as JobOptions;
use Spiral\Jobs\QueueInterface;
use Spiral\Mailer\MailerInterface;
use Spiral\Mailer\MessageInterface;
use Spiral\Queue\Options;
use Spiral\SendIt\Config\MailerConfig;

final class MailQueue implements MailerInterface
{
    public const JOB_NAME = 'sendit.mail';

    /** @var MailerConfig */
    private $config;

    /** @var QueueInterface|\Spiral\Queue\QueueInterface */
    private $queue;

    public function __construct(MailerConfig $config, $queue)
    {
        $this->config = $config;
        $this->queue = $queue;
    }

    public function send(MessageInterface ...$message): void
    {
        if ($this->queue instanceof QueueInterface) {
            $options = (new JobOptions())->withPipeline($this->config->getQueue());
        } else {
            $options = Options::onQueue($this->config->getQueue());
        }

        foreach ($message as $msg) {
            $this->queue->push(
                self::JOB_NAME,
                MessageSerializer::pack($msg),
                $options->withDelay($msg->getOptions()['delay'] ?? null)
            );
        }
    }
}

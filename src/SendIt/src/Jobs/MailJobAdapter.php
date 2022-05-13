<?php

declare(strict_types=1);

namespace Spiral\SendIt\Jobs;

use Psr\Log\LoggerInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Jobs\HandlerInterface;

/**
 * @deprecated since 2.13. Will be removed since 3.0
 */
final class MailJobAdapter implements HandlerInterface, SingletonInterface
{
    private \Spiral\SendIt\MailJob $job;

    public function __construct(\Spiral\SendIt\MailJob $job)
    {
        $this->job = $job;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->job->setLogger($logger);
    }

    public function handle(string $name, string $id, $payload): void
    {
        $this->job->handle($name, $id, $payload);
    }
}

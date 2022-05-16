<?php

declare(strict_types=1);

namespace Spiral\SendIt\Jobs;

use Psr\Log\LoggerInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Jobs\HandlerInterface;
use Spiral\SendIt\MailJob;

/**
 * @deprecated since 2.13. Will be removed since 3.0
 */
final class MailJobAdapter implements HandlerInterface, SingletonInterface
{
    private MailJob $job;

    public function __construct(MailJob $job)
    {
        $this->job = $job;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->job->setLogger($logger);
    }

    public function handle(string $jobType, string $jobID, $payload): void
    {
        $this->job->handle($jobType, $jobID, $payload);
    }
}

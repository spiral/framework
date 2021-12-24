<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Jobs\JobsBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Jobs\JobRegistry;
use Spiral\Mailer\MailerInterface;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Spiral\SendIt\MessageSerializer;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mailer\Transport;

/**
 * Enables email sending pipeline.
 */
final class MailerBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        JobsBootloader::class,
    ];

    protected const SINGLETONS = [
        MailerInterface::class => MailQueue::class,
        MailJob::class         => MailJob::class,
        SymfonyMailer::class   => [self::class, 'mailer'],
    ];

    /** @var ConfiguratorInterface */
    private $config;

    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    public function boot(EnvironmentInterface $env, JobRegistry $jobRegistry): void
    {
        $this->config->setDefaults('mailer', [
            'dsn'      => $env->get('MAILER_DSN', ''),
            'pipeline' => $env->get('MAILER_PIPELINE', 'local'),
            'from'     => $env->get('MAILER_FROM', 'Spiral <sendit@local.host>'),
        ]);

        $jobRegistry->setHandler(MailQueue::JOB_NAME, MailJob::class);
        $jobRegistry->setSerializer(MailQueue::JOB_NAME, MessageSerializer::class);
    }

    public function mailer(MailerConfig $config): SymfonyMailer
    {
        $transport = Transport::fromDsn($config->getDSN());

        return new Mailer($transport);
    }
}

<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Jobs\JobsBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Jobs\JobRegistry;
use Spiral\Mailer\MailerInterface;
use Spiral\Queue\Bootloader\QueueBootloader;
use Spiral\Queue\HandlerRegistryInterface;
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
        QueueBootloader::class,
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

    public function boot(EnvironmentInterface $env, AbstractKernel $kernel): void
    {
        $this->config->setDefaults('mailer', [
            'dsn'      => $env->get('MAILER_DSN', ''),
            'pipeline' => $env->get('MAILER_PIPELINE', 'local'),
            'from'     => $env->get('MAILER_FROM', 'Spiral <sendit@local.host>'),
        ]);
    }

    public function start(ContainerInterface $container): void
    {
        if ($container->has(JobRegistry::class)) {
            // Will be removed since v3.0
            $registry = $container->get(JobRegistry::class);
            $registry->setHandler(MailQueue::JOB_NAME, MailJob::class);
            $registry->setSerializer(MailQueue::JOB_NAME, MessageSerializer::class);
        }

        if ($container->has(HandlerRegistryInterface::class)) {
            $registry = $container->get(HandlerRegistryInterface::class);
            $registry->setHandler(MailQueue::JOB_NAME, MailJob::class);
        }
    }

    public function mailer(MailerConfig $config): SymfonyMailer
    {
        $transport = Transport::fromDsn($config->getDSN());

        return new Mailer($transport);
    }
}

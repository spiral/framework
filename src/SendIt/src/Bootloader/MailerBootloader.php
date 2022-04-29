<?php

declare(strict_types=1);

namespace Spiral\SendIt\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Mailer\MailerInterface;
use Spiral\Queue\Bootloader\QueueBootloader;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mailer\Transport;

/**
 * Enables email sending pipeline.
 */
final class MailerBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        QueueBootloader::class,
    ];

    protected const SINGLETONS = [
        MailJob::class => MailJob::class,
        SymfonyMailer::class => [self::class, 'mailer'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(EnvironmentInterface $env): void
    {
        $queue = $env->get('MAILER_QUEUE', 'local');

        $this->config->setDefaults(MailerConfig::CONFIG, [
            'dsn' => $env->get('MAILER_DSN', ''),
            'queue' => $queue,
            'from' => $env->get('MAILER_FROM', 'Spiral <sendit@local.host>'),
            'queueConnection' => $env->get('MAILER_QUEUE_CONNECTION'),
        ]);
    }

    public function boot(Container $container): void
    {
        $container->bindSingleton(
            MailerInterface::class,
            static fn (MailerConfig $config, QueueConnectionProviderInterface $provider): MailQueue => new MailQueue(
                $config,
                $provider->getConnection($config->getQueueConnection())
            )
        );

        if ($container->has(HandlerRegistryInterface::class)) {
            $registry = $container->get(HandlerRegistryInterface::class);
            $registry->setHandler(MailQueue::JOB_NAME, MailJob::class);
        }
    }

    public function mailer(MailerConfig $config): SymfonyMailer
    {
        return new Mailer(
            Transport::fromDsn($config->getDSN())
        );
    }
}

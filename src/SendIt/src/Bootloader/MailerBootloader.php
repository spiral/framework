<?php

declare(strict_types=1);

namespace Spiral\SendIt\Bootloader;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\BinderInterface;
use Spiral\Mailer\MailerInterface;
use Spiral\Queue\Bootloader\QueueBootloader;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Queue\QueueRegistry;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Enables email sending pipeline.
 */
class MailerBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        QueueBootloader::class,
        BuilderBootloader::class,
    ];

    protected const SINGLETONS = [
        MailJob::class => MailJob::class,
        SymfonyMailer::class => [self::class, 'mailer'],
        TransportInterface::class => [self::class, 'initTransport'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(MailerConfig::CONFIG, [
            'dsn' => $env->get('MAILER_DSN', ''),
            'queue' => $env->get('MAILER_QUEUE', 'local'),
            'from' => $env->get('MAILER_FROM', 'Spiral <sendit@local.host>'),
            'queueConnection' => $env->get('MAILER_QUEUE_CONNECTION'),
        ]);
    }

    public function boot(BinderInterface $binder, ContainerInterface $container): void
    {
        $binder->bindSingleton(
            MailerInterface::class,
            static fn (MailerConfig $config, QueueConnectionProviderInterface $provider): MailQueue => new MailQueue(
                $config,
                $provider->getConnection($config->getQueueConnection())
            )
        );

        $registry = $container->get(QueueRegistry::class);
        \assert($registry instanceof QueueRegistry);
        $registry->setHandler(MailQueue::JOB_NAME, MailJob::class);
    }

    public function initTransport(MailerConfig $config): TransportInterface
    {
        return Transport::fromDsn($config->getDSN());
    }

    public function mailer(TransportInterface $transport, ?EventDispatcherInterface $dispatcher = null): SymfonyMailer
    {
        return new Mailer(
            transport: $transport,
            dispatcher: $dispatcher
        );
    }
}

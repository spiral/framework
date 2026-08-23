<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Bootloader\SendIt;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Mailer\MailerInterface;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\SendIt\Bootloader\MailerBootloader;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Spiral\SendIt\TransportRegistryInterface;
use Spiral\SendIt\TransportResolver;
use Spiral\SendIt\TransportResolverInterface;
use Spiral\Tests\Framework\BaseTestCase;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class MailerBootloaderTest extends BaseTestCase
{
    public const ENV = [
        'MAILER_DSN' => 'smtp://user:pass@smtp.example.com:25',
        'MAILER_QUEUE' => 'testing',
        'MAILER_FROM' => 'Testing <testing@local.host>',
        'MAILER_QUEUE_CONNECTION' => 'sync',
    ];

    public function testBootloaderIsNotFinal(): void
    {
        $class = new \ReflectionClass(MailerBootloader::class);

        /**
         * {@see https://github.com/spiral/framework/pull/683}
         */
        self::assertFalse($class->isFinal(), 'MailerBootloader should not be final.');
    }

    public function testTransportResolverBindings(): void
    {
        $this->assertContainerBoundAsSingleton(TransportResolver::class, TransportResolver::class);
        $this->assertContainerBoundAsSingleton(TransportResolverInterface::class, TransportResolver::class);
        $this->assertContainerBoundAsSingleton(TransportRegistryInterface::class, TransportResolver::class);
    }

    public function testMailJobBinding(): void
    {
        $this->assertContainerBoundAsSingleton(MailJob::class, MailJob::class);
    }

    public function testSymfonyMailerBinding(): void
    {
        $this->assertContainerBoundAsSingleton(SymfonyMailer::class, Mailer::class);
    }

    public function testMailerInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(MailerInterface::class, MailQueue::class);
    }

    public function testMailQueueBinding(): void
    {
        $this->assertContainerBoundAsSingleton(MailQueue::class, MailQueue::class);
    }

    public function testMailerInterfaceAndMailQueueShareTheSameInstance(): void
    {
        self::assertSame(
            $this->getContainer()->get(MailQueue::class),
            $this->getContainer()->get(MailerInterface::class),
        );
    }

    public function testMailQueueUsesConfiguredQueueConnection(): void
    {
        $mailQueue = $this->getContainer()->get(MailQueue::class);
        $queue = (new \ReflectionProperty($mailQueue, 'queue'))->getValue($mailQueue);

        self::assertSame(
            $this->getContainer()->get(QueueConnectionProviderInterface::class)->getConnection('sync'),
            $queue,
        );
    }

    public function testSubclassCanOverrideBindingConstants(): void
    {
        $bootloader = new class($this->getContainer()->get(ConfiguratorInterface::class)) extends MailerBootloader {
            protected const DEPENDENCIES = [];
            protected const SINGLETONS = parent::SINGLETONS + ['foo' => 'bar'];
        };

        self::assertSame([], $bootloader->defineDependencies());
        self::assertSame('bar', $bootloader->defineSingletons()['foo']);
        self::assertSame(MailQueue::class, $bootloader->defineSingletons()[MailerInterface::class]);
    }

    public function testTransportInterfaceBinding(): void
    {
        $this->assertContainerBoundAsSingleton(TransportInterface::class, SmtpTransport::class);
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(MailerConfig::CONFIG, [
            'dsn' => 'smtp://user:pass@smtp.example.com:25',
            'queue' => 'testing',
            'from' => 'Testing <testing@local.host>',
            'queueConnection' => 'sync',
        ]);
    }
}

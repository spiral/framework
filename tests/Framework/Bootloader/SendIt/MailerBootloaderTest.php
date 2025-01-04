<?php

declare(strict_types=1);

namespace Framework\Bootloader\SendIt;

use Spiral\Mailer\MailerInterface;
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

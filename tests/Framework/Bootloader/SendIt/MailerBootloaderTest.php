<?php

declare(strict_types=1);

namespace Framework\Bootloader\SendIt;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Spiral\Mailer\MailerInterface;
use Spiral\SendIt\Bootloader\MailerBootloader;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Spiral\Tests\Framework\BaseTest;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\NullTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class MailerBootloaderTest extends BaseTest
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
        $this->assertFalse($class->isFinal(), 'MailerBootloader should not be final.');
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
            'transportFactories' => [],
        ]);
    }

    /**
     * Only NativeTransportFactory is supported.
     */
    public function testCustomTransportFactoriesForSmtpNotFount(): void
    {
        $this->expectException(UnsupportedSchemeException::class);
        $this->updateConfig(MailerConfig::CONFIG.'.transportFactories', [
            NativeTransportFactory::class,
        ]);
        $this->getContainer()->get(TransportInterface::class);
    }

    /**
     * Only EsmtpTransportFactory is supported.
     */
    public function testCustomTransportFactoriesSmtpOnly(): void
    {
        $this->updateConfig(MailerConfig::CONFIG.'.transportFactories', [
            EsmtpTransportFactory::class,
        ]);
        $this->assertContainerBoundAsSingleton(TransportInterface::class, SmtpTransport::class);
    }

    public function testCustomTransportFactoriesNullSupported(): void
    {
        $this->updateConfig(MailerConfig::CONFIG.'.transportFactories', [
            NullTransportFactory::class,
        ]);
        $this->updateConfig(MailerConfig::CONFIG.'.dsn', 'null://default');
        $this->assertContainerBoundAsSingleton(TransportInterface::class, NullTransport::class);
    }

    public function testCustomTransportHasDI(): void
    {
        $this->assertContainerMissed(PsrEventDispatcherInterface::class);
        $this->assertContainerMissed(PsrLoggerInterface::class);

        $dispatcher = $this->createMock(PsrEventDispatcherInterface::class);
        $logger = $this->createMock(PsrLoggerInterface::class);
        $this->getContainer()->bind(PsrEventDispatcherInterface::class, $dispatcher);
        $this->getContainer()->bind(PsrLoggerInterface::class, $logger);

        $this->updateConfig(MailerConfig::CONFIG.'.transportFactories', [
            EsmtpTransportFactory::class,
        ]);
        $this->assertContainerBoundAsSingleton(TransportInterface::class, SmtpTransport::class);

        $transport = $this->getContainer()->get(TransportInterface::class);
        $this->assertInstanceOf(SmtpTransport::class, $transport);

        $class = new \ReflectionClass(AbstractTransport::class);
        // dispatcher
        $prop = $class->getProperty('dispatcher');
        $prop->setAccessible(true);
        $this->assertSame($dispatcher, $prop->getValue($transport));
        // logger
        $prop = $class->getProperty('logger');
        $prop->setAccessible(true);
        $this->assertSame($logger, $prop->getValue($transport));
    }

    public function testCustomTransportHasNotDI(): void
    {
        $this->assertContainerMissed(PsrEventDispatcherInterface::class);
        $this->assertContainerMissed(PsrLoggerInterface::class);

        $this->updateConfig(MailerConfig::CONFIG.'.transportFactories', [
            EsmtpTransportFactory::class,
        ]);
        $this->assertContainerBoundAsSingleton(TransportInterface::class, SmtpTransport::class);

        $transport = $this->getContainer()->get(TransportInterface::class);
        $this->assertInstanceOf(SmtpTransport::class, $transport);

        $class = new \ReflectionClass(AbstractTransport::class);
        // dispatcher
        $prop = $class->getProperty('dispatcher');
        $prop->setAccessible(true);
        $this->assertSame(null, $prop->getValue($transport));
        // logger
        $prop = $class->getProperty('logger');
        $prop->setAccessible(true);
        $this->assertInstanceOf(\Psr\Log\NullLogger::class, $prop->getValue($transport));
    }
}

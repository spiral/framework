<?php

declare(strict_types=1);

namespace Framework\Bootloader\SendIt;

use Spiral\Mailer\MailerInterface;
use Spiral\SendIt\Config\MailerConfig;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Spiral\Tests\Framework\BaseTest;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailer;

final class MailerBootloaderTest extends BaseTest
{
    public const ENV = [
        'MAILER_DSN' => 'smtp://user:pass@smtp.example.com:25',
        'MAILER_QUEUE' => 'testing',
        'MAILER_FROM' => 'Testing <testing@local.host>',
        'MAILER_QUEUE_CONNECTION' => 'sync',
    ];

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

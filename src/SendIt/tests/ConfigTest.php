<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\Environment;
use Spiral\SendIt\Config\MailerConfig;

class ConfigTest extends TestCase
{
    public function testConfig(): void
    {
        $cfg = new MailerConfig([
            'dsn' => 'mailer-dsn',
            'from' => 'admin@spiral.dev',
            'queue' => 'emails',
            'queueConnection' => 'foo',
        ]);

        $this->assertSame('mailer-dsn', $cfg->getDSN());
        $this->assertSame('admin@spiral.dev', $cfg->getFromAddress());
        $this->assertSame('emails', $cfg->getQueue());
        $this->assertSame('foo', $cfg->getQueueConnection());
    }

    public function testWithDeprecatedPipeline(): void
    {
        $cfg = new MailerConfig([
            'pipeline' => 'emails',
        ]);

        $this->assertSame('emails', $cfg->getQueuePipeline());
    }

    public function testDefaultConfig(): void
    {
        $env = new Environment();

        $config = new MailerConfig([
            'dsn' => $env->get('MAILER_DSN', ''),
            'pipeline' => $env->get('MAILER_QUEUE', $env->get('MAILER_PIPELINE', 'local')),
            'queue' => $env->get('MAILER_QUEUE', 'local'),
            'from' => $env->get('MAILER_FROM', 'Spiral <sendit@local.host>'),
            'queueConnection' => $env->get('MAILER_QUEUE_CONNECTION'),
        ]);

        $this->assertSame('', $config->getDSN());
        $this->assertSame('Spiral <sendit@local.host>', $config->getFromAddress());
        $this->assertSame('local', $config->getQueue());
        $this->assertNull($config->getQueueConnection());
    }

    public function testDefaultConfigWithQueue(): void
    {
        $env = new Environment(['MAILER_QUEUE' => 'emails']);

        $config = new MailerConfig([
            'queue' => $env->get('MAILER_QUEUE', 'local'),
        ]);

        $this->assertSame('emails', $config->getQueue());
        $this->assertSame('emails', $config->getQueuePipeline());
    }

    public function testDefaultConfigWithPippeline(): void
    {
        $env = new Environment(['MAILER_PIPELINE' => 'emails']);

        $config = new MailerConfig([
            'pipeline' => $env->get('MAILER_QUEUE', $env->get('MAILER_PIPELINE', 'local')),
        ]);

        $this->assertSame('emails', $config->getQueue());
        $this->assertSame('emails', $config->getQueuePipeline());
    }

    public function testGetsQueueConnectionWithoutKey(): void
    {
        $cfg = new MailerConfig();
        $this->assertNull($cfg->getQueueConnection());
    }
}

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
use Spiral\SendIt\Config\MailerConfig;

class ConfigTest extends TestCase
{
    public function testConfig(): void
    {
        $cfg = new MailerConfig([
            'dsn' => 'mailer-dsn',
            'from' => 'admin@spiral.dev',
            'pipeline' => 'emails',
            'queueConnection' => 'foo',
        ]);

        $this->assertSame('mailer-dsn', $cfg->getDSN());
        $this->assertSame('admin@spiral.dev', $cfg->getFromAddress());
        $this->assertSame('emails', $cfg->getQueuePipeline());
        $this->assertSame('foo', $cfg->getQueueConnection());
    }

    public function testGetsQueueConnectionWithoutKey(): void
    {
        $cfg = new MailerConfig();
        $this->assertNull($cfg->getQueueConnection());
    }
}

<?php

declare(strict_types=1);

namespace Spiral\Tests\Logger;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Spiral\Logger\Event\LogEvent;
use Spiral\Logger\ListenerRegistry;
use Spiral\Logger\LogFactory;

class FactoryTest extends TestCase
{
    public function testEvent(): void
    {
        $l = new ListenerRegistry();
        $l->addListener(function (LogEvent $event): void {
            $this->assertSame('error', $event->getMessage());
            $this->assertSame('default', $event->getChannel());
            $this->assertSame(LogLevel::CRITICAL, $event->getLevel());
        });

        $f = new LogFactory($l);

        $l = $f->getLogger('default');

        $l->critical('error');
    }
}

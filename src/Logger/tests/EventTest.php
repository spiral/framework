<?php

declare(strict_types=1);

namespace Spiral\Tests\Logger;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Spiral\Logger\Event\LogEvent;

class EventTest extends TestCase
{
    public function testListenDebug(): void
    {
        $e = new LogEvent(
            new \DateTime(),
            'default',
            LogLevel::DEBUG,
            'message',
            ['context']
        );

        $this->assertInstanceOf(\DateTimeInterface::class, $e->getTime());
        $this->assertSame('default', $e->getChannel());
        $this->assertSame(LogLevel::DEBUG, $e->getLevel());
        $this->assertSame('message', $e->getMessage());
        $this->assertSame(['context'], $e->getContext());
    }
}

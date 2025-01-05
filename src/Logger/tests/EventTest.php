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

        self::assertInstanceOf(\DateTimeInterface::class, $e->getTime());
        self::assertSame('default', $e->getChannel());
        self::assertSame(LogLevel::DEBUG, $e->getLevel());
        self::assertSame('message', $e->getMessage());
        self::assertSame(['context'], $e->getContext());
    }
}

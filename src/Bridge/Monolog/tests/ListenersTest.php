<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Spiral\Core\Container;
use Spiral\Logger\Event\LogEvent;
use Spiral\Logger\ListenerRegistry;
use Spiral\Monolog\Config\MonologConfig;
use Spiral\Monolog\LogFactory;

class ListenersTest extends TestCase
{
    public function testListenDebug(): void
    {
        $factory = new LogFactory(new MonologConfig([
            'globalLevel' => Logger::DEBUG
        ]), $l = new ListenerRegistry(), new Container());

        $logger = $factory->getLogger();
        $other = $factory->getLogger('other');

        /** @var LogEvent[]|array $records */
        $records = [];
        $l->addListener(function (LogEvent $e) use (&$records): void {
            $records[] = $e;
        });

        $logger->debug('debug');
        $other->alert('alert', ['context']);

        $this->assertCount(2, $records);
        $this->assertInstanceOf(\DateTimeInterface::class, $records[0]->getTime());
        $this->assertSame('default', $records[0]->getChannel());
        $this->assertSame(LogLevel::DEBUG, $records[0]->getLevel());
        $this->assertSame('debug', $records[0]->getMessage());
        $this->assertSame([], $records[0]->getContext());

        $this->assertSame('other', $records[1]->getChannel());
        $this->assertSame(LogLevel::ALERT, $records[1]->getLevel());
        $this->assertSame('alert', $records[1]->getMessage());
        $this->assertSame(['context'], $records[1]->getContext());
    }

    public function testListenError(): void
    {
        $factory = new LogFactory(new MonologConfig([
            'globalLevel' => Logger::ERROR
        ]), $ll = new ListenerRegistry(), new Container());

        $logger = $factory->getLogger();
        $other = $factory->getLogger('other');

        /** @var LogEvent[]|array $records */
        $records = [];
        $ll->addListener($l = function (LogEvent $e) use (&$records): void {
            $records[] = $e;
        });

        $logger->debug('debug');
        $other->alert('alert', ['context']);

        $this->assertCount(1, $records);

        $this->assertInstanceOf(\DateTimeInterface::class, $records[0]->getTime());
        $this->assertSame('other', $records[0]->getChannel());
        $this->assertSame(LogLevel::ALERT, $records[0]->getLevel());
        $this->assertSame('alert', $records[0]->getMessage());
        $this->assertSame(['context'], $records[0]->getContext());

        $ll->removeListener($l);

        $logger->debug('debug');
        $other->alert('alert', ['context']);
        $this->assertCount(1, $records);
    }
}

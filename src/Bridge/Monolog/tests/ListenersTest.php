<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Monolog\Logger;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
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
            'globalLevel' => Logger::DEBUG,
        ]), $l = new ListenerRegistry(), new Container());

        $logger = $factory->getLogger();
        $other = $factory->getLogger('other');

        /** @var LogEvent[]|array $records */
        $records = [];
        $l->addListener(static function (LogEvent $e) use (&$records): void {
            $records[] = $e;
        });

        $logger->debug('debug');
        $other->alert('alert', ['context']);

        self::assertCount(2, $records);
        self::assertInstanceOf(\DateTimeInterface::class, $records[0]->getTime());
        self::assertSame('default', $records[0]->getChannel());
        self::assertSame(LogLevel::DEBUG, $records[0]->getLevel());
        self::assertSame('debug', $records[0]->getMessage());
        self::assertSame([], $records[0]->getContext());

        self::assertSame('other', $records[1]->getChannel());
        self::assertSame(LogLevel::ALERT, $records[1]->getLevel());
        self::assertSame('alert', $records[1]->getMessage());
        self::assertSame(['context'], $records[1]->getContext());
    }

    public function testListenError(): void
    {
        $factory = new LogFactory(new MonologConfig([
            'globalLevel' => Logger::ERROR,
        ]), $ll = new ListenerRegistry(), new Container());

        $logger = $factory->getLogger();
        $other = $factory->getLogger('other');

        /** @var LogEvent[]|array $records */
        $records = [];
        $ll->addListener($l = static function (LogEvent $e) use (&$records): void {
            $records[] = $e;
        });

        $logger->debug('debug');
        $other->alert('alert', ['context']);

        self::assertCount(1, $records);

        self::assertInstanceOf(\DateTimeInterface::class, $records[0]->getTime());
        self::assertSame('other', $records[0]->getChannel());
        self::assertSame(LogLevel::ALERT, $records[0]->getLevel());
        self::assertSame('alert', $records[0]->getMessage());
        self::assertSame(['context'], $records[0]->getContext());

        $ll->removeListener($l);

        $logger->debug('debug');
        $other->alert('alert', ['context']);
        self::assertCount(1, $records);
    }

    #[DoesNotPerformAssertions]
    public function testRemoveNotExistingListener(): void
    {
        $registry = new ListenerRegistry();

        $registry->removeListener(static fn(LogEvent $e) => null);
    }
}

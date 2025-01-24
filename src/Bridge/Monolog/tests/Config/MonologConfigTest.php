<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog\Config;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use PHPUnit\Framework\TestCase;
use Spiral\Monolog\Config\MonologConfig;

final class MonologConfigTest extends TestCase
{
    public function testGetDefault(): void
    {
        $config = new MonologConfig();
        self::assertSame(MonologConfig::DEFAULT_CHANNEL, $config->getDefault());

        $config = new MonologConfig(['default' => 'foo']);
        self::assertSame('foo', $config->getDefault());
    }

    public function testGetEventLevel(): void
    {
        $config = new MonologConfig();
        self::assertSame(Logger::DEBUG, $config->getEventLevel());

        $config = new MonologConfig(['globalLevel' => Logger::INFO]);
        self::assertSame(Logger::INFO, $config->getEventLevel());
    }

    public function testGetHandlers(): void
    {
        $config = new MonologConfig();
        self::assertEmpty(\iterator_to_array($config->getHandlers('foo')));

        $config = new MonologConfig([
            'handlers' => [
                'foo' => [
                    $this->createMock(HandlerInterface::class),
                ],
            ],
        ]);
        self::assertInstanceOf(HandlerInterface::class, \iterator_to_array($config->getHandlers('foo'))[0]);
    }

    public function testGetProcessors(): void
    {
        $config = new MonologConfig();
        self::assertEmpty(\iterator_to_array($config->getProcessors('foo')));

        $config = new MonologConfig([
            'processors' => [
                'foo' => [
                    $this->createMock(ProcessorInterface::class),
                ],
            ],
        ]);
        self::assertInstanceOf(ProcessorInterface::class, \iterator_to_array($config->getProcessors('foo'))[0]);
    }
}

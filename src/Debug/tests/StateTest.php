<?php

declare(strict_types=1);

namespace Spiral\Tests\Debug;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Spiral\Debug\Exception\StateException;
use Spiral\Debug\State;
use Spiral\Logger\Event\LogEvent;

class StateTest extends TestCase
{
    public function testTags(): void
    {
        $state = new State();
        self::assertSame([], $state->getTags());

        $state->setTag('key', 'value');
        self::assertSame([
            'key' => 'value'
        ], $state->getTags());

        $state->setTag('key2', 'value');
        self::assertSame([
            'key'  => 'value',
            'key2' => 'value'
        ], $state->getTags());

        $state->setTag('key', 'value2');
        self::assertSame([
            'key'  => 'value2',
            'key2' => 'value'
        ], $state->getTags());

        $state->setTags(['a' => 'b']);

        self::assertSame([
            'a' => 'b',
        ], $state->getTags());

        $state->reset();

        self::assertSame([], $state->getTags());
    }

    public function testTagsException(): void
    {
        $state = new State();
        $this->expectException(StateException::class);
        $state->setTags(['aa' => 11]);
    }

    public function testExtras(): void
    {
        $state = new State();
        self::assertSame([], $state->getVariables());

        $state->setVariable('key', 'value');
        self::assertSame([
            'key' => 'value'
        ], $state->getVariables());

        $state->setVariable('key2', 'value');
        self::assertSame([
            'key'  => 'value',
            'key2' => 'value'
        ], $state->getVariables());


        $state->setVariable('key', 'value2');
        self::assertSame([
            'key'  => 'value2',
            'key2' => 'value'
        ], $state->getVariables());

        $state->setVariables(['a' => 'b']);

        self::assertSame([
            'a' => 'b',
        ], $state->getVariables());

        $state->reset();

        self::assertSame([], $state->getVariables());
    }

    public function testLogEvents(): void
    {
        $state = new State();
        self::assertSame([], $state->getLogEvents());

        $state->addLogEvent(new LogEvent(
            new \DateTime(),
            'default',
            LogLevel::ERROR,
            'message'
        ));
        self::assertCount(1, $state->getLogEvents());

        $state->addLogEvent(
            new LogEvent(
                new \DateTime(),
                'default1',
                LogLevel::ERROR,
                'message'
            ),
            new LogEvent(
                new \DateTime(),
                'default1',
                LogLevel::ERROR,
                'message'
            )
        );

        self::assertCount(3, $state->getLogEvents());

        $state->reset();
        self::assertSame([], $state->getLogEvents());
    }
}

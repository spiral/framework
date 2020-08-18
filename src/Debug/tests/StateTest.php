<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
        $this->assertEquals([], $state->getTags());

        $state->setTag('key', 'value');
        $this->assertEquals([
            'key' => 'value'
        ], $state->getTags());

        $state->setTag('key2', 'value');
        $this->assertEquals([
            'key'  => 'value',
            'key2' => 'value'
        ], $state->getTags());

        $state->setTag('key', 'value2');
        $this->assertEquals([
            'key'  => 'value2',
            'key2' => 'value'
        ], $state->getTags());

        $state->setTags(['a' => 'b']);

        $this->assertEquals([
            'a' => 'b',
        ], $state->getTags());

        $state->reset();

        $this->assertEquals([], $state->getTags());
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
        $this->assertEquals([], $state->getVariables());

        $state->setVariable('key', 'value');
        $this->assertEquals([
            'key' => 'value'
        ], $state->getVariables());

        $state->setVariable('key2', 'value');
        $this->assertEquals([
            'key'  => 'value',
            'key2' => 'value'
        ], $state->getVariables());


        $state->setVariable('key', 'value2');
        $this->assertEquals([
            'key'  => 'value2',
            'key2' => 'value'
        ], $state->getVariables());

        $state->setVariables(['a' => 'b']);

        $this->assertEquals([
            'a' => 'b',
        ], $state->getVariables());

        $state->reset();

        $this->assertEquals([], $state->getVariables());
    }

    public function testLogEvents(): void
    {
        $state = new State();
        $this->assertEquals([], $state->getLogEvents());

        $state->addLogEvent(new LogEvent(
            new \DateTime(),
            'default',
            LogLevel::ERROR,
            'message'
        ));
        $this->assertCount(1, $state->getLogEvents());

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

        $this->assertCount(3, $state->getLogEvents());

        $state->reset();
        $this->assertEquals([], $state->getLogEvents());
    }
}

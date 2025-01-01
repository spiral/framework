<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting;

use PHPUnit\Framework\TestCase;
use Spiral\Broadcasting\TopicRegistry;

final class TopicRegistryTest extends TestCase
{
    private TopicRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new TopicRegistry([
            'bar-topic.{id}' => fn($id) => $id,
            'foo-topic' => static fn(): string => 'foo',
        ]);
    }

    public function testRegisterTopic(): void
    {
        $this->registry->register('baz-topic', static fn(): string => 'baz');
        $this->registry->register('baz.{uuid}', static fn(string $uuid): string => $uuid);


        $params = [];
        $this->assertSame(
            'baz',
            call_user_func($this->registry->findCallback('baz-topic', $params))
        );


        $params = [];
        $this->assertSame(
            'hello',
            call_user_func($this->registry->findCallback('baz.hello', $params), 'hello')
        );
        $this->assertSame([0 => 'baz.hello', 'uuid' => 'hello', 1 => 'hello'], $params);
    }

    public function testFindsTopicCallback(): void
    {
        $params = [];
        $this->assertSame(
            'foo',
            call_user_func($this->registry->findCallback('foo-topic', $params))
        );

        $this->assertSame([0 => 'foo-topic'], $params);

        $params = [];
        $this->assertSame(
            5,
            call_user_func($this->registry->findCallback('bar-topic.5', $params), 5)
        );
        $this->assertSame([0 => 'bar-topic.5', 'id' => '5', 1 => '5'], $params);


        $params = [];
        $this->assertNull(
            $this->registry->findCallback('baz-topic', $params)
        );
        $this->assertSame([], $params);
    }
}

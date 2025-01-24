<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting;

use PHPUnit\Framework\TestCase;
use Spiral\Broadcasting\TopicRegistry;

final class TopicRegistryTest extends TestCase
{
    private TopicRegistry $registry;

    public function testRegisterTopic(): void
    {
        $this->registry->register('baz-topic', static fn(): string => 'baz');
        $this->registry->register('baz.{uuid}', static fn(string $uuid): string => $uuid);


        $params = [];
        self::assertSame('baz', call_user_func($this->registry->findCallback('baz-topic', $params)));


        $params = [];
        self::assertSame('hello', call_user_func($this->registry->findCallback('baz.hello', $params), 'hello'));
        self::assertSame([0 => 'baz.hello', 'uuid' => 'hello', 1 => 'hello'], $params);
    }

    public function testFindsTopicCallback(): void
    {
        $params = [];
        self::assertSame('foo', call_user_func($this->registry->findCallback('foo-topic', $params)));

        self::assertSame([0 => 'foo-topic'], $params);

        $params = [];
        self::assertSame(5, call_user_func($this->registry->findCallback('bar-topic.5', $params), 5));
        self::assertSame([0 => 'bar-topic.5', 'id' => '5', 1 => '5'], $params);


        $params = [];
        self::assertNull($this->registry->findCallback('baz-topic', $params));
        self::assertSame([], $params);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new TopicRegistry([
            'bar-topic.{id}' => fn($id) => $id,
            'foo-topic' => static fn(): string => 'foo',
        ]);
    }
}

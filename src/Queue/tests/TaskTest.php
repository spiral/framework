<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Queue\Task;

final class TaskTest extends TestCase
{
    public function testGetPayload(): void
    {
        $task = new Task('some-id', 'some-queue', 'some-name', ['key' => 'value'], ['header' => ['value']]);
        $this->assertSame(['key' => 'value'], $task->getPayload());

        $task = new Task('some-id', 'some-queue', 'some-name', 'string-payload', ['header' => ['value']]);
        $this->assertSame('string-payload', $task->getPayload());
    }

    public function testGetName(): void
    {
        $task = new Task('some-id', 'some-queue', 'some-name', ['key' => 'value'], ['header' => ['value']]);
        $this->assertSame('some-name', $task->getName());
    }

    public function testGetHeaders(): void
    {
        $task = new Task('some-id', 'some-queue', 'some-name', ['key' => 'value'], ['header' => ['value']]);
        $this->assertSame(['header' => ['value']], $task->getHeaders());
    }

    public function testGetQueue(): void
    {
        $task = new Task('some-id', 'some-queue', 'some-name', ['key' => 'value'], ['header' => ['value']]);
        $this->assertSame('some-queue', $task->getQueue());
    }

    public function testGetId(): void
    {
        $task = new Task('some-id', 'some-queue', 'some-name', ['key' => 'value'], ['header' => ['value']]);
        $this->assertSame('some-id', $task->getId());
    }
}

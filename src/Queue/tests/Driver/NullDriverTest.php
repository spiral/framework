<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Driver;

use Spiral\Queue\Driver\NullDriver;
use Spiral\Tests\Queue\TestCase;

final class NullDriverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->queue = new NullDriver();
    }

    public function testJobShouldBePushed(): void
    {
        $id = $this->queue->push('foo', ['foo' => 'bar']);
        $this->assertNotNull($id);
    }

    public function testJobObjectShouldBePushed(): void
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        $id = $this->queue->pushObject($object);
        $this->assertNotNull($id);
    }

    public function testJobCallableShouldBePushed(): void
    {
        $callback = function () {
            return 'bar';
        };

        $id = $this->queue->pushCallable($callback);
        $this->assertNotNull($id);
    }
}

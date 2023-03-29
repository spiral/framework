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

    /**
     * @dataProvider PayloadDataProvider
     */
    public function testJobShouldBePushed(mixed $payload): void
    {
        $id = $this->queue->push('foo', $payload);
        $this->assertNotNull($id);
    }

    public function PayloadDataProvider(): \Traversable
    {
        yield [['baz' => 'baf']];
        yield [new \stdClass()];
        yield ['some string'];
        yield [123];
        yield [null];
    }
}

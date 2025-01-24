<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Driver;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Queue\Driver\NullDriver;
use Spiral\Tests\Queue\TestCase;

final class NullDriverTest extends TestCase
{
    private NullDriver $queue;

    public static function payloadDataProvider(): \Traversable
    {
        yield [['baz' => 'baf']];
        yield [new \stdClass()];
        yield ['some string'];
        yield [123];
        yield [null];
    }

    #[DataProvider('payloadDataProvider')]
    public function testJobShouldBePushed(mixed $payload): void
    {
        $id = $this->queue->push('foo', $payload);
        self::assertNotNull($id);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->queue = new NullDriver();
    }
}

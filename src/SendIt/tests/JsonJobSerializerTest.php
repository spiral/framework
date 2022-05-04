<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use PHPUnit\Framework\TestCase;
use Spiral\SendIt\JsonJobSerializer;

final class JsonJobSerializerTest extends TestCase
{
    public function testSerialize(): void
    {
        $data = ['foo' => 'bar'];

        $this->assertSame('{"foo":"bar"}', (new JsonJobSerializer())->serialize('baz', $data));
    }
}

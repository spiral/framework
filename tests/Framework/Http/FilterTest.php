<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Tests\Framework\HttpTestCase;

final class FilterTest extends HttpTestCase
{
    public function testValid(): void
    {
        $this->getHttp()
            ->post('/filter', data: ['name' => 'hello'])
            ->assertBodySame('{"name":"hello","sectionValue":null}');
    }

    public function testDotNotation(): void
    {
        $this->getHttp()
            ->post('/filter', data: ['name' => 'hello', 'section' => ['value' => 'abc'],])
            ->assertBodySame('{"name":"hello","sectionValue":"abc"}');
    }

    public function testBadRequest(): void
    {
        $this->getHttp()->get('/filter2')->assertStatus(500);
    }

    public function testInputTest(): void
    {
        $this->getHttp()
            ->get('/input', query: ['section' => ['value' => 'abc'],])
            ->assertBodySame('value: abc');
    }
}

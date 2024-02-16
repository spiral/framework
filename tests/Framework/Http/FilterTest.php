<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

#[TestScope(Spiral::Http)]
final class FilterTest extends HttpTestCase
{
    public function testValid(): void
    {
        $this
            ->fakeHttp()
            ->post('/filter', data: ['name' => 'hello'])
            ->assertBodySame('{"name":"hello","sectionValue":null}');
    }

    public function testDotNotation(): void
    {
        $this
            ->fakeHttp()
            ->post('/filter', data: ['name' => 'hello', 'section' => ['value' => 'abc'],])
            ->assertBodySame('{"name":"hello","sectionValue":"abc"}');
    }

    public function testBadRequest(): void
    {
        $this->fakeHttp()
            ->get('/filter2')
            ->assertStatus(500);
    }

    public function testInputTest(): void
    {
        $this
            ->fakeHttp()
            ->get('/input', query: ['section' => ['value' => 'abc'],])
            ->assertBodySame('value: abc');
    }
}

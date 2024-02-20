<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Spiral\Testing\Attribute\TestScope;

#[TestScope('http')]
final class IntegrationTest extends TestCase
{
    public function testRoute(): void
    {
        $this->fakeHttp()->get('/')->assertBodySame('index');
    }

    public function testRoute2(): void
    {
        $this->fakeHttp()->post('/')->assertBodySame('method');
    }

    public function testRoute3(): void
    {
        $this->fakeHttp()->get('/page/test')->assertBodySame('page-test');
    }

    public function testRoute4(): void
    {
        $this->fakeHttp()->get('/page/about')->assertBodySame('about');
    }

    public function testRoutesWithoutNames(): void
    {
        $this->fakeHttp()->get('/nameless')->assertBodySame('index');
        $this->fakeHttp()->post('/nameless')->assertBodySame('method');
        $this->fakeHttp()->get('/nameless/route')->assertBodySame('route');
    }
}

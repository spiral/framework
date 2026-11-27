<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Http\Request\InputManager;

final class SlicesTest extends TestCase
{
    private Container $container;
    private InputManager $input;

    public function testNoSlice(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('GET', ''))->withParsedBody([
            'array' => [
                'key' => 'value',
            ],
        ]));

        self::assertSame([
            'array' => [
                'key' => 'value',
            ],
        ], $this->input->data->all());
    }

    public function testSlice(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('GET', ''))->withParsedBody([
            'array' => [
                'key' => 'value',
            ],
        ]));

        self::assertSame([
            'key' => 'value',
        ], $this->input->withPrefix('array')->data->all());
    }

    public function testDeadEnd(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('GET', ''))->withParsedBody([
            'array' => [
                'key' => 'value',
            ],
        ]));

        self::assertSame([], $this->input->withPrefix('other')->data->all());
    }

    public function testMultiple(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('GET', ''))->withParsedBody([
            'array' => [
                'key' => [
                    'name' => 'value',
                ],
            ],
        ]));

        self::assertSame([
            'name' => 'value',
        ], $this->input->withPrefix('array.key')->data->all());

        $input = $this->input->withPrefix('array');

        self::assertSame([
            'key' => [
                'name' => 'value',
            ],
        ], $input->data->all());

        $input = $input->withPrefix('key');

        self::assertSame([
            'name' => 'value',
        ], $input->data->all());

        $input = $input->withPrefix('', false);

        self::assertSame([
            'array' => [
                'key' => [
                    'name' => 'value',
                ],
            ],
        ], $input->data->all());

        self::assertSame('value', $input->data('array.key.name'));
        self::assertSame('value', $input->post('array.key.name'));
    }

    public function testChainOfSlices(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('GET', ''))->withParsedBody([
            'a' => ['0' => ['b' => ['name' => 'value', 'empty' => null]]],
        ]));

        $input = $this->input->withPrefix('a')->withPrefix('0')->withPrefix('b');

        self::assertSame('value', $input->data('name'));
        self::assertSame('value', $input->post('name'));
        self::assertTrue($input->post->has('empty'));
        self::assertSame(['name' => 'value', 'empty' => null], $input->data->all());
        self::assertSame([], $input->withPrefix('missing')->withPrefix('deeper')->data->all());
    }

    public function testAbsolutePrefixAfterChain(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('GET', ''))->withParsedBody([
            'a' => ['b' => ['name' => 'nested']],
            'c' => ['name' => 'root'],
        ]));

        $input = $this->input->withPrefix('a')->withPrefix('b');
        self::assertSame('nested', $input->data('name'));

        self::assertSame('root', $input->withPrefix('c', false)->data('name'));
        self::assertSame('root', $input->withPrefix('c', false)->withPrefix('', true)->data('name'));
    }

    public function testSliceFollowsChangedRequest(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('GET', ''))->withParsedBody([
            'a' => ['b' => ['name' => 'first']],
        ]));

        $parent = $this->input->withPrefix('a');
        $child = $parent->withPrefix('b');
        self::assertSame(['b' => ['name' => 'first']], $parent->data->all());
        self::assertSame('first', $child->data('name'));

        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('GET', ''))->withParsedBody([
            'a' => ['b' => ['name' => 'second']],
        ]));

        self::assertSame('second', $child->data('name'));
        self::assertSame(['b' => ['name' => 'second']], $parent->data->all());
    }

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->input = new InputManager($this->container);
    }
}

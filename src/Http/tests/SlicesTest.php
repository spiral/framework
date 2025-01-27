<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Http\Request\InputManager;
use Nyholm\Psr7\ServerRequest;

class SlicesTest extends TestCase
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

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->input = new InputManager($this->container);
    }
}
